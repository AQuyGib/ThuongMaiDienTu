<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Services\TranslationService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware tự động dịch toàn bộ phản hồi HTML tĩnh, phản hồi JSON (API),
 * các thuộc tính JSON props của các component React/Vue, và chuỗi text tĩnh trong thẻ <script>.
 */
class TranslateHtmlResponse
{
    // Service xử lý dịch thuật (gọi API dịch Google GTX miễn phí hoặc Google Cloud)
    protected TranslationService $translator;

    /**
     * Khởi tạo Middleware và inject dịch vụ dịch thuật.
     */
    public function __construct(TranslationService $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Điểm chặn xử lý Request và Response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
 
        // CHỈ thực hiện dịch khi ngôn ngữ hiện tại của ứng dụng được chọn là Tiếng Anh ('en')
        if (App::getLocale() !== 'en') {
            return $response;
        }
 
        // Bỏ qua các phản hồi điều hướng (Redirect) hoặc lỗi hệ thống nghiêm trọng (500...)
        if ($response->isRedirection() || $response->isServerError()) {
            return $response;
        }
 
        // Kiểm tra xem phản hồi trả về có phải định dạng JSON (API/AJAX) hay không
        $contentType = $response->headers->get('Content-Type');
        $isJson = ($response instanceof \Illuminate\Http\JsonResponse) || (strpos($contentType, 'application/json') !== false);
 
        if ($isJson) {
            $content = $response->getContent();
            if (!blank($content)) {
                $data = json_decode($content, true);
                if (is_array($data)) {
                    // Tăng thời gian thực thi tối đa lên 120s phòng trường hợp dịch dữ liệu JSON lớn trong lần đầu
                    @set_time_limit(120);
                    $translatedData = $this->translateJsonArray($data);
                    
                    // Cập nhật lại nội dung dịch vào JsonResponse hoặc JSON string tương ứng
                    if ($response instanceof \Illuminate\Http\JsonResponse) {
                        $response->setData($translatedData);
                    } else {
                        $response->setContent(json_encode($translatedData, JSON_UNESCAPED_UNICODE));
                    }
                }
            }
            return $response;
        }
 
        // Nếu không phải HTML thì bỏ qua không dịch (ví dụ: file tải về, ảnh, css, js...)
        if (strpos($contentType, 'text/html') === false) {
            return $response;
        }
 
        $content = $response->getContent();
        if (blank($content)) {
            return $response;
        }
 
        // Thiết lập thời gian tối đa thực thi (120 giây) để tránh timeout do dịch trang HTML nhiều thẻ chữ
        @set_time_limit(120);
 
        // Thực hiện phân tích và dịch nội dung HTML
        $translatedContent = $this->translateHtml($content);
        $response->setContent($translatedContent);
 
        return $response;
    }

    /**
     * Dịch toàn bộ trang HTML bằng DOMDocument.
     */
    protected function translateHtml(string $html): string
    {
        // Bước 1: Trích xuất và ẩn các thẻ <script> để tránh DOMDocument phân tích sai hoặc làm hỏng mã JS
        $scripts = [];
        $html = preg_replace_callback('/<script\b[^>]*>(.*?)<\/script>/is', function ($matches) use (&$scripts) {
            $placeholder = '<!-- [SCRIPT_PLACEHOLDER_' . count($scripts) . '] -->';
            $scripts[] = $matches[0];
            return $placeholder;
        }, $html);

        // Bước 2: Trích xuất và ẩn các thẻ <style> để tránh DOMDocument làm hỏng định dạng CSS
        $styles = [];
        $html = preg_replace_callback('/<style\b[^>]*>(.*?)<\/style>/is', function ($matches) use (&$styles) {
            $placeholder = '<!-- [STYLE_PLACEHOLDER_' . count($styles) . '] -->';
            $styles[] = $matches[0];
            return $placeholder;
        }, $html);

        // Khởi tạo thư viện DOMDocument để duyệt và chỉnh sửa HTML
        $dom = new \DOMDocument();
        
        // Vô hiệu hóa lỗi cảnh báo nội bộ (libxml errors) giúp bỏ qua cảnh báo của các thẻ HTML5 mới
        libxml_use_internal_errors(true);
        
        // Nạp HTML với khai báo UTF-8 giúp giữ nguyên các ký tự có dấu Tiếng Việt
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        libxml_clear_errors();

        // [PASS 1]: Thu thập tất cả các chuỗi Tiếng Việt chưa dịch trên trang (quét text node và thuộc tính)
        $untranslated = [];
        $this->collectUntranslatedStrings($dom, $untranslated);

        // Thu thập thêm các chuỗi văn bản Tiếng Việt tĩnh nằm trong các thẻ <script> đã trích xuất
        foreach ($scripts as $scriptContent) {
            $this->collectUntranslatedJsStrings($scriptContent, $untranslated);
        }

        // [PASS 2]: Gửi yêu cầu dịch gộp (Batch Translation) các chuỗi thu thập được và lưu vào cache trọn đời
        if (!empty($untranslated)) {
            $this->batchTranslate($untranslated);
        }

        // [PASS 3]: Duyệt lại cây DOM và thay thế các node văn bản bằng các bản dịch đã có trong cache
        $this->translateNode($dom, $this->translator);

        // Xuất HTML đã dịch ra dạng chuỗi văn bản
        $output = $dom->saveHTML();

        // Khôi phục lại các khối <style> nguyên bản ban đầu
        foreach ($styles as $index => $styleContent) {
            $output = str_replace('<!-- [STYLE_PLACEHOLDER_' . $index . '] -->', $styleContent, $output);
        }

        // Khôi phục lại các khối <script> (đã được lọc dịch các chuỗi text tĩnh bên trong)
        foreach ($scripts as $index => $scriptContent) {
            $translatedScript = $this->translateJavascriptStrings($scriptContent, $this->translator);
            $output = str_replace('<!-- [SCRIPT_PLACEHOLDER_' . $index . '] -->', $translatedScript, $output);
        }

        return $output;
    }

    /**
     * Quét đệ quy cây DOM để thu thập tất cả các chuỗi cần dịch chưa có trong cache.
     */
    protected function collectUntranslatedStrings(\DOMNode $node, array &$untranslated): void
    {
        // Bỏ qua các thẻ chứa mã code, style, script không cần hiển thị hoặc dịch nội dung
        if ($node->nodeType === XML_ELEMENT_NODE && in_array(strtolower($node->nodeName), ['script', 'style', 'code', 'pre'])) {
            return;
        }

        // Thu thập các thuộc tính hiển thị văn bản (placeholder, title, alt) & thuộc tính chứa dữ liệu JSON (React props)
        if ($node->nodeType === XML_ELEMENT_NODE && $node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                $attrName = strtolower($attr->name);
                if (in_array($attrName, ['placeholder', 'title', 'alt'])) {
                    $text = $attr->value;
                    if ($this->shouldTranslate($text)) {
                        $trimmed = $this->getTrimmedText($text);
                        if ($trimmed !== '' && !isset($untranslated[$trimmed]) && !\Illuminate\Support\Facades\Cache::has('html_trans_' . md5($trimmed))) {
                            $untranslated[$trimmed] = true;
                        }
                    }
                } elseif ($attrName === 'value' && strtolower($node->nodeName) === 'input') {
                    // Chỉ dịch thuộc tính value của các nút bấm, không dịch của các ô input text thông thường
                    $type = strtolower($node->getAttribute('type') ?? '');
                    if (in_array($type, ['submit', 'button', 'reset'])) {
                        $text = $attr->value;
                        if ($this->shouldTranslate($text)) {
                            $trimmed = $this->getTrimmedText($text);
                            if ($trimmed !== '' && !isset($untranslated[$trimmed]) && !\Illuminate\Support\Facades\Cache::has('html_trans_' . md5($trimmed))) {
                                $untranslated[$trimmed] = true;
                            }
                        }
                    }
                } elseif ($attrName === 'data-props' || strpos($attrName, 'props') !== false || strpos($attrName, 'data-') === 0) {
                    // Giải mã thực thể HTML và dịch đệ quy các chuỗi văn bản nằm trong thuộc tính JSON của React
                    $jsonStr = html_entity_decode($attr->value, ENT_QUOTES, 'UTF-8');
                    $trimmedJson = trim($jsonStr);
                    if ($trimmedJson !== '' && (strpos($trimmedJson, '{') === 0 || strpos($trimmedJson, '[') === 0)) {
                        $decoded = json_decode($jsonStr, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $this->collectUntranslatedJsonStrings($decoded, $untranslated);
                        }
                    }
                }
            }
        }

        // Bỏ qua việc duyệt thẻ con của <textarea> để không dịch dữ liệu người dùng nhập
        if ($node->nodeType === XML_ELEMENT_NODE && strtolower($node->nodeName) === 'textarea') {
            return;
        }

        // Đệ quy duyệt các thẻ con, nếu là Text node thì lấy nội dung dịch
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                $this->collectUntranslatedStrings($child, $untranslated);
            }
        } elseif ($node->nodeType === XML_TEXT_NODE) {
            $text = $node->nodeValue;
            if ($this->shouldTranslate($text)) {
                $trimmed = $this->getTrimmedText($text);
                if ($trimmed !== '' && !isset($untranslated[$trimmed]) && !\Illuminate\Support\Facades\Cache::has('html_trans_' . md5($trimmed))) {
                    $untranslated[$trimmed] = true;
                }
            }
        }
    }

    /**
     * Thay thế giá trị của các Node bằng bản dịch lấy từ Cache.
     */
    protected function translateNode(\DOMNode $node, TranslationService $translator): void
    {
        if ($node->nodeType === XML_ELEMENT_NODE && in_array(strtolower($node->nodeName), ['script', 'style', 'code', 'pre'])) {
            return;
        }

        if ($node->nodeType === XML_ELEMENT_NODE && $node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                $attrName = strtolower($attr->name);
                if (in_array($attrName, ['placeholder', 'title', 'alt'])) {
                    $text = $attr->value;
                    if ($this->shouldTranslate($text)) {
                        $attr->value = $this->getCachedTranslation($text, $translator);
                    }
                } elseif ($attrName === 'value' && strtolower($node->nodeName) === 'input') {
                    $type = strtolower($node->getAttribute('type') ?? '');
                    if (in_array($type, ['submit', 'button', 'reset'])) {
                        $text = $attr->value;
                        if ($this->shouldTranslate($text)) {
                            $attr->value = $this->getCachedTranslation($text, $translator);
                        }
                    }
                } elseif ($attrName === 'data-props' || strpos($attrName, 'props') !== false || strpos($attrName, 'data-') === 0) {
                    $jsonStr = html_entity_decode($attr->value, ENT_QUOTES, 'UTF-8');
                    $trimmedJson = trim($jsonStr);
                    if ($trimmedJson !== '' && (strpos($trimmedJson, '{') === 0 || strpos($trimmedJson, '[') === 0)) {
                        $decoded = json_decode($jsonStr, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $translatedDecoded = $this->translateJsonArray($decoded);
                            // Lưu lại JSON an toàn với định dạng HTML entities
                            $attr->value = json_encode($translatedDecoded, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
                        }
                    }
                }
            }
        }

        if ($node->nodeType === XML_ELEMENT_NODE && strtolower($node->nodeName) === 'textarea') {
            return;
        }

        if ($node->hasChildNodes()) {
            $children = [];
            foreach ($node->childNodes as $child) {
                $children[] = $child;
            }
            foreach ($children as $child) {
                $this->translateNode($child, $translator);
            }
        } elseif ($node->nodeType === XML_TEXT_NODE) {
            $text = $node->nodeValue;
            if ($this->shouldTranslate($text)) {
                $translatedText = $this->getCachedTranslation($text, $translator);
                $node->nodeValue = $translatedText;
            }
        }
    }

    /**
     * Xác định xem một chuỗi văn bản có hợp lệ để đem đi dịch hay không.
     */
    protected function shouldTranslate(string $text): bool
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return false;
        }
        
        // Bỏ qua nếu chuỗi không chứa bất kỳ chữ cái nào (chỉ toàn ký hiệu hoặc khoảng trắng)
        if (!preg_match('/[\p{L}]/u', $trimmed)) {
            return false;
        }

        // Bỏ qua nếu chuỗi chỉ gồm số, ký hiệu tiền tệ, phần trăm, dấu ngăn cách (Ví dụ: "15.000.000đ" hoặc "50%")
        if (preg_match('/^[\d\s\p{Sc}%\.,:\-\/\+đđVNDvnd]+$/ui', $trimmed)) {
            return false;
        }

        // Bỏ qua các ký tự đơn (tránh dịch các ký hiệu layout hoặc icon chữ lẻ)
        if (mb_strlen($trimmed) <= 1) {
            return false;
        }

        // Bỏ qua nếu là đường dẫn liên kết URL
        if (preg_match('/^(https?:\/\/|\/\/)/i', $trimmed)) {
            return false;
        }

        // Bỏ qua nếu là tên file/ảnh (có đuôi tệp tin mở rộng)
        if (preg_match('/\.(jpg|jpeg|png|gif|webp|svg|css|js|ico|pdf|zip)$/i', $trimmed)) {
            return false;
        }

        // Bỏ qua các thư mục lưu trữ hệ thống (như /storage/ hoặc /assets/)
        if (preg_match('/^\/(storage|assets|uploads|images|css|js)\//i', $trimmed)) {
            return false;
        }

        return true;
    }

    /**
     * Tách bỏ khoảng trắng ở đầu và cuối chuỗi để gom nhóm dịch chuẩn xác.
     */
    protected function getTrimmedText(string $text): string
    {
        preg_match('/^(\s*)(.*?)(\s*)$/us', $text, $matches);
        return $matches[2] ?? '';
    }

    /**
     * Dịch gộp hàng loạt (Batch translation) tối ưu hóa API: 
     * Gộp 15 chuỗi văn bản vào 1 yêu cầu dịch duy nhất ngăn cách bởi dấu xuống dòng.
     */
    protected function batchTranslate(array $untranslated): void
    {
        $strings = array_keys($untranslated);
        
        // Chia nhóm mỗi lần dịch 15 chuỗi văn bản
        $chunks = array_chunk($strings, 15);

        foreach ($chunks as $chunk) {
            // Thay thế ký tự xuống dòng thực tế bằng khoảng trắng để tránh lỗi chia hàng
            $cleanChunk = array_map(function ($str) {
                return str_replace(["\r", "\n"], ' ', $str);
            }, $chunk);

            $combined = implode("\n", $cleanChunk);
            
            // Gửi một yêu cầu duy nhất dịch toàn bộ cụm văn bản
            $translatedCombined = $this->translator->translate($combined, 'vi', 'en');

            // Phân tách kết quả dịch trả về theo dòng
            $translatedLines = explode("\n", $translatedCombined);

            // Kiểm tra tính đối xứng của kết quả dịch
            if (count($translatedLines) === count($cleanChunk)) {
                foreach ($cleanChunk as $index => $originalStr) {
                    $translatedStr = trim($translatedLines[$index]);
                    if ($translatedStr === '') {
                        $translatedStr = $originalStr;
                    }
                    // Lưu bản dịch vào cache vĩnh viễn (remember forever) bằng key md5
                    \Illuminate\Support\Facades\Cache::forever('html_trans_' . md5($originalStr), $translatedStr);
                }
            } else {
                // Fallback: Dịch riêng lẻ từng chuỗi nếu số dòng dịch ra bị lệch
                foreach ($cleanChunk as $originalStr) {
                    $translatedStr = $this->translator->translate($originalStr, 'vi', 'en');
                    \Illuminate\Support\Facades\Cache::forever('html_trans_' . md5($originalStr), $translatedStr);
                }
            }
        }
    }

    /**
     * Lấy bản dịch đã lưu trong Cache và khôi phục các khoảng trắng nguyên bản ở đầu/cuối chuỗi.
     */
    protected function getCachedTranslation(string $text, TranslationService $translator): string
    {
        preg_match('/^(\s*)(.*?)(\s*)$/us', $text, $matches);
        $leadingWs = $matches[1] ?? '';
        $trimmedText = $matches[2] ?? '';
        $trailingWs = $matches[3] ?? '';

        if ($trimmedText === '') {
            return $text;
        }

        $cacheKey = 'html_trans_' . md5($trimmedText);
        $translatedTrimmed = \Illuminate\Support\Facades\Cache::get($cacheKey, $trimmedText);

        return $leadingWs . $translatedTrimmed . $trailingWs;
    }

    /**
     * Dịch đệ quy các giá trị dạng mảng/đối tượng JSON.
     */
    protected function translateJsonArray(array $array): array
    {
        $untranslated = [];
        $this->collectUntranslatedJsonStrings($array, $untranslated);

        if (!empty($untranslated)) {
            $this->batchTranslate($untranslated);
        }

        return $this->translateJsonArrayValues($array);
    }

    /**
     * Thu thập chuỗi Tiếng Việt chưa dịch nằm trong mảng JSON.
     */
    protected function collectUntranslatedJsonStrings(array $array, array &$untranslated): void
    {
        foreach ($array as $key => $value) {
            if (is_string($key) && $this->isMachineKey($key)) {
                continue;
            }
            if (is_array($value)) {
                $this->collectUntranslatedJsonStrings($value, $untranslated);
            } elseif (is_string($value)) {
                if ($this->shouldTranslate($value)) {
                    $trimmed = $this->getTrimmedText($value);
                    if ($trimmed !== '' && !isset($untranslated[$trimmed]) && !\Illuminate\Support\Facades\Cache::has('html_trans_' . md5($trimmed))) {
                        $untranslated[$trimmed] = true;
                    }
                }
            }
        }
    }

    /**
     * Gán bản dịch vào mảng JSON.
     */
    protected function translateJsonArrayValues(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_string($key) && $this->isMachineKey($key)) {
                continue;
            }
            if (is_array($value)) {
                $array[$key] = $this->translateJsonArrayValues($value);
            } elseif (is_string($value)) {
                if ($this->shouldTranslate($value)) {
                    $array[$key] = $this->getCachedTranslation($value, $this->translator);
                }
            }
        }
        return $array;
    }

    /**
     * Kiểm tra xem một key JSON có phải là trường máy tính (machine key) không cần dịch hay không.
     */
    protected function isMachineKey(string $key): bool
    {
        $keyLower = strtolower($key);
        
        // Các key kết thúc bằng _id hoặc bắt đầu bằng is_
        if (preg_match('/_id$/', $keyLower) || preg_match('/^is_/', $keyLower)) {
            return true;
        }

        $blacklist = [
            'status', 'success', 'code', 'error_code', 'action', 'type', 'id',
            'role', 'email', 'username', 'phone', 'thumbnail', 'image', 'path',
            'file', 'mime_type', 'extension', 'url', 'redirect', 'route', 'key',
            'field', 'slug', 'locale', 'lang', 'created_at', 'updated_at',
            'deleted_at', 'date', 'time', 'size', 'file_size', 'avatar', 'icon',
            'color', 'rom', 'rom_capacity', 'extra_price'
        ];

        return in_array($keyLower, $blacklist);
    }

    /**
     * Phát hiện các hằng chuỗi Tiếng Việt tĩnh nằm trong mã script của thẻ <script>.
     */
    protected function collectUntranslatedJsStrings(string $script, array &$untranslated): void
    {
        // Biểu thức chính quy quét các ký tự có dấu Tiếng Việt
        $viRegex = '/[\x{00C0}-\x{00C3}\x{00C8}-\x{00CA}\x{00CC}-\x{00CD}\x{00D2}-\x{00D5}\x{00D9}-\x{00DA}\x{00DD}\x{00E0}-\x{00E3}\x{00E8}-\x{00EA}\x{00EC}-\x{00ED}\x{00F2}-\x{00F5}\x{00F9}-\x{00FA}\x{00FD}\x{0102}-\x{0103}\x{0110}-\x{0111}\x{0128}-\x{0129}\x{0168}-\x{0169}\x{01A0}-\x{01A1}\x{01AF}-\x{01B0}\x{1EA0}-\x{1EF9}]/u';
        
        $patterns = [
            '/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/u', // Tìm chuỗi nháy kép "..."
            '/\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/u', // Tìm chuỗi nháy đơn '...'
            '/`([^`\\\\]*(?:\\\\.[^`\\\\]*)*)`/u'   // Tìm chuỗi nháy ngược `...` (template literals)
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $script, $matches)) {
                foreach ($matches[1] as $strContent) {
                    if (preg_match($viRegex, $strContent)) {
                        if ($this->shouldTranslate($strContent)) {
                            $trimmed = $this->getTrimmedText($strContent);
                            if ($trimmed !== '' && !isset($untranslated[$trimmed]) && !\Illuminate\Support\Facades\Cache::has('html_trans_' . md5($trimmed))) {
                                $untranslated[$trimmed] = true;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Dịch và thay thế an toàn các chuỗi Tiếng Việt trong mã nguồn Javascript của thẻ <script>.
     */
    protected function translateJavascriptStrings(string $scriptContent, TranslationService $translator): string
    {
        $viRegex = '/[\x{00C0}-\x{00C3}\x{00C8}-\x{00CA}\x{00CC}-\x{00CD}\x{00D2}-\x{00D5}\x{00D9}-\x{00DA}\x{00DD}\x{00E0}-\x{00E3}\x{00E8}-\x{00EA}\x{00EC}-\x{00ED}\x{00F2}-\x{00F5}\x{00F9}-\x{00FA}\x{00FD}\x{0102}-\x{0103}\x{0110}-\x{0111}\x{0128}-\x{0129}\x{0168}-\x{0169}\x{01A0}-\x{01A1}\x{01AF}-\x{01B0}\x{1EA0}-\x{1EF9}]/u';
        
        $patterns = [
            '/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/u',
            '/\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/u',
            '/`([^`\\\\]*(?:\\\\.[^`\\\\]*)*)`/u'
        ];
        
        $replacements = [];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $scriptContent, $matches)) {
                foreach ($matches[1] as $index => $strContent) {
                    $fullMatch = $matches[0][$index];
                    if (preg_match($viRegex, $strContent)) {
                        if ($this->shouldTranslate($strContent)) {
                            $translatedStr = $this->getCachedTranslation($strContent, $translator);
                            $quoteChar = $fullMatch[0];
                            // Escape dấu nháy của chuỗi tránh gây lỗi cú pháp JS khi thay thế ngược lại
                            $escapedTrans = addcslashes($translatedStr, $quoteChar . "\\");
                            $newStr = $quoteChar . $escapedTrans . $quoteChar;
                            $replacements[$fullMatch] = $newStr;
                        }
                    }
                }
            }
        }
        
        // Thực hiện thay thế hàng loạt
        if (!empty($replacements)) {
            $scriptContent = strtr($scriptContent, $replacements);
        }
        
        return $scriptContent;
    }
}
