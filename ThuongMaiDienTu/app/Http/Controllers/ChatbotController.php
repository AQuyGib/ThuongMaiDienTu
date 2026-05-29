<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ChatbotController - API Chatbot RAG (Retrieval-Augmented Generation)
 *
 * Luồng xử lý:
 * 1. Nhận câu hỏi từ Frontend (AJAX POST)
 * 2. Trích xuất từ khóa & tìm kiếm sản phẩm liên quan trong DB (LIKE)
 * 3. Gửi câu hỏi + ngữ cảnh (sản phẩm tìm được) lên Gemini API
 * 4. Trả JSON response cho Frontend
 */
class ChatbotController extends Controller
{
    /**
     * Danh sách các model Gemini fallback (thử lần lượt)
     */
    private array $models = [
        'gemini-3.1-flash-lite',
        'gemini-3.5-flash',
        'gemini-3-flash-preview',
        'gemini-2.5-flash',
    ];

    /**
     * Các từ vô nghĩa (stop words) sẽ bị lọc bỏ khi trích xuất từ khóa
     */
    private array $stopwords = [
        'là', 'gì', 'cho', 'tôi', 'hỏi', 'có', 'không', 'giá', 'bao', 'nhiêu',
        'tư', 'vấn', 'cái', 'này', 'xin', 'chào', 'mua', 'bán', 'nào', 'của',
        'và', 'hay', 'hoặc', 'thì', 'mà', 'với', 'được', 'các', 'một', 'những',
        'đây', 'đó', 'kia', 'ạ', 'nhé', 'nha', 'ơi', 'thế', 'sao',
        'recommend', 'cheap', 'under', 'which', 'is', 'suitable', 'for', 'student', 'students', 'what', 'the', 'a', 'an', 'to', 'in', 'of', 'and', 'with', 'about', 'some', 'any', 'help', 'you', 'me', 'i', 'how', 'show', 'list', 'find', 'search', 'currently', 'on', 'promotion', 'promotions',
        'do', 'does', 'are', 'can', 'could', 'would', 'should', 'will', 'want', 'looking', 'buy', 'get', 'have', 'has', 'please', 'tell', 'suggest', 'give', 'best', 'good', 'better', 'top', 'most', 'popular', 'product', 'products', 'items', 'item', 'device', 'devices', 'like', 'interested', 'in', 'recommendation', 'recommendations', 'advisor', 'advise'
    ];

    /**
     * Xử lý request chat từ Frontend
     */
    public function chat(Request $request)
    {
        $prompt = trim($request->input('prompt', ''));
        $currentProductContext = trim($request->input('context', ''));

        if (!$prompt) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập câu hỏi.',
            ]);
        }

        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa cấu hình API Key cho AI.',
            ]);
        }

        // Tự động phát hiện ngôn ngữ dựa trên câu hỏi của khách hàng (không phụ thuộc vào locale của trang web)
        $detectedLang = $this->detectLanguage($prompt);
        $isEnglish = ($detectedLang === 'en');

        // BƯỚC 1: RAG - Trích xuất từ khóa & tìm kiếm sản phẩm trong DB (sử dụng ngôn ngữ đã phát hiện)
        $productKnowledge = $this->searchProducts($prompt, $isEnglish);

        // BƯỚC 2: Chuẩn bị prompt & gọi Gemini API
        if ($isEnglish) {
            $contextInstruction = 'The customer is on the Homepage or browsing general categories. Please provide a general consultation.';
            if ($currentProductContext) {
                $contextInstruction = "SPECIAL NOTICE: The customer is VIEWING THIS PRODUCT:\n{$currentProductContext}\n-> Prioritize using this product's details in your response.";
            }

            $fullPrompt = "BACKGROUND: You are the Smart Shopping Assistant of DIENMAY PRO - a retail system for phones, laptops, and technology accessories.
INVENTORY SEARCH RESULTS RELATED TO THE QUESTION:
{$productKnowledge}

STORE POLICY CONTEXT (Services, Warranty, Return, Installment, Points & Rewards):
- WARRANTY: 12 - 24 months official warranty depending on the device. Customers check warranty status at: <a href=\"/warranty\" class=\"chatbot-product-link\">Warranty lookup</a> or detailed rules at <a href=\"/chinh-sach-bao-hanh\" class=\"chatbot-product-link\">Warranty policy</a>.
- RETURN & REFUND: 1-to-1 replacement or free refund within 30 days for manufacturer defects. Details at <a href=\"/chinh-sach-doi-tra\" class=\"chatbot-product-link\">Return policy</a>.
- INSTALLMENT: 0% interest installment via credit cards (Visa, MasterCard, JCB via OnePay gateway with no conversion fee) or Kredivo. Also financial company installment with down payment from 30%. Apply directly by clicking \"0% Installment\" on product detail page.
- POINTS & REWARDS: Shop to accumulate points for membership tier-up (Bronze, Silver, Gold, Diamond). Points can be used to redeem discount vouchers, shipping vouchers, real gifts, or play Lucky Wheel at: <a href=\"/rewards\" class=\"chatbot-product-link\">Rewards Catalog</a>.

RESPONSE RULES (CRITICAL - MANDATORY):
1. LANGUAGE RULE (HIGHEST PRIORITY): The customer is querying in English. You MUST respond 100% in English. Under no circumstances should you write any Vietnamese sentence, phrase, or word (except brand names or product names if they have no English equivalent). Your entire response, including the greeting, analysis, policy explanations, and closing, MUST be written in English.

2. MANDATORY RESPONSE STRUCTURE (must be followed in all languages):
   INTRODUCTION: Warm greeting, empathy with customer needs. Briefly summarize key aspects to consider when selecting products.
   ANALYSIS SECTIONS (2-3 paragraphs): Categorize suggestions by USE CASE (not by product categories). Start each section with an emoji (👉, ✅, 💻, 📱), followed by in-depth explanation of why it fits, then mention the brand/product with a link. NEVER list products dryly like a shopping list.
   POLICY SECTION: Naturally integrate warranty, return, installment policy into the context (e.g., \"To give you peace of mind, we support up to 24 months warranty...\"), DO NOT list policies separately like an information sheet.
   CONCLUSION: Warm call to action, invite the customer to share more information (major/field of study, budget, etc.) for better advice. Lightly mention the rewards program.

3. PROFESSIONAL CONSULTING STYLE:
   - Talk like an expert consultant chatting directly with the customer, NOT like a machine listing products.
   - Give VALUABLE advice: Explain WHY the product is suitable, NOT JUST the name and price.
   - Show understanding of customer needs, use friendly, warm, and attentive language.
   - NEVER drop the entire product list into a long block of text. Only mention 2-4 most relevant products, distributed across the analysis sections.

4. LINK INSERTION RULES (NEVER MAKE BROKEN LINKS - MANDATORY):
    - All product detail links MUST use the HTML link format with the exact URL prefix \"/san-pham/\" followed by the product ID, and MUST have class \"chatbot-product-link\" with the product name as the link text: <a href=\"/san-pham/ID\" class=\"chatbot-product-link\">Product Name</a> (Example: <a href=\"/san-pham/16\" class=\"chatbot-product-link\">ASUS ROG Strix G16 2024</a>). DO NOT display the link as raw text, and DO NOT use the URL as the link text. DO NOT translate \"san-pham\" to \"product\", \"products\", \"en/san-pham\", or anything else in the href attribute.
    - All brand, category, or product type links MUST use the exact search format: <a href=\"/search?q=keyword\" class=\"chatbot-product-link\">keyword_name</a> (Example: <a href=\"/search?q=Asus\" class=\"chatbot-product-link\">Asus</a>, <a href=\"/search?q=Dell\" class=\"chatbot-product-link\">Dell</a>, <a href=\"/search?q=MacBook\" class=\"chatbot-product-link\">MacBook</a>). DO NOT translate or change the \"/search?q=\" path.
    - All policy links MUST keep the exact path provided in the STORE POLICY CONTEXT above (e.g. \"/chinh-sach-bao-hanh\", \"/chinh-sach-doi-tra\", \"/warranty\", \"/rewards\"). DO NOT translate these paths (e.g., do not use \"/warranty-policy\" or \"/return-policy\" inside the href attribute).
    - NEVER make up links like /san-pham/brand-name or /san-pham/category-name. All brands and categories must go through the /search?q=keyword link.

5. DO NOT USE MARKDOWN: Do not use **, -, #, * or any Markdown syntax in the response.
6. USE HTML TAGS FOR FORMATTING:
   - Bold title or important keywords: <b>content</b>
   - Line break: <br>
7. FORMATTING RULES:
   - Between main paragraphs, use EXACTLY ONE <br> tag for spacing. DO NOT use multiple consecutive <br> tags.
   - Start each analysis paragraph with an emoji (👉, ✅, 📱, 💻, 🌟).
   - Divide into 3-4 short, easy-to-read paragraphs.

{$contextInstruction}

CUSTOMER'S QUESTION: {$prompt}";
        } else {
            $contextInstruction = 'Khách hàng đang ở Trang chủ hoặc xem danh mục chung. Hãy tư vấn tổng quan.';
            if ($currentProductContext) {
                $contextInstruction = "ĐẶC BIỆT LƯU Ý: Khách hàng ĐANG XEM SẢN PHẨM NÀY:\n{$currentProductContext}\n-> Ưu tiên dùng thông tin sản phẩm này để trả lời.";
            }

            $fullPrompt = "BỐI CẢNH: Bạn là Trợ lý bán hàng thông minh của DIENMAY PRO - hệ thống bán lẻ điện thoại, laptop, phụ kiện công nghệ.
NGỮ CẢNH KHO HÀNG (Dữ liệu từ Database):
{$productKnowledge}

NGỮ CẢNH CHÍNH SÁCH CỬA HÀNG (Dịch vụ, Bảo hành, Đổi trả, Trả góp, Tích điểm):
- BẢO HÀNH: Bảo hành chính hãng từ 12 - 24 tháng tùy dòng máy. Khách hàng kiểm tra thời hạn bảo hành trực tiếp tại: <a href=\"/warranty\" class=\"chatbot-product-link\">Tra cứu bảo hành</a> hoặc quy định chi tiết tại <a href=\"/chinh-sach-bao-hanh\" class=\"chatbot-product-link\">Chính sách bảo hành</a>.
- ĐỔI TRẢ & HOÀN TIỀN: Hỗ trợ 1 đổi 1 hoặc hoàn tiền miễn phí trong vòng 30 ngày nếu phát sinh lỗi từ nhà sản xuất. Chi tiết tại <a href=\"/chinh-sach-doi-tra\" class=\"chatbot-product-link\">Chính sách đổi trả</a>.
- TRẢ GÓP: Hỗ trợ trả góp 0% lãi suất qua thẻ tín dụng (Visa, MasterCard, JCB qua cổng OnePay không phí chuyển đổi) hoặc qua Kredivo. Ngoài ra có trả góp qua các công ty tài chính với mức trả trước từ 30%. Khách hàng đăng ký trực tiếp bằng cách nhấn nút \"Trả góp 0%\" tại trang chi tiết sản phẩm.
- TÍCH ĐIỂM & ĐỔI THƯỞNG: Mua sắm tích lũy điểm để nâng hạng thành viên (Đồng, Bạc, Vàng, Kim Cương). Điểm dùng đổi Voucher giảm giá, Voucher vận chuyển hoặc quà tặng thật, hoặc tham gia Vòng quay may mắn tại: <a href=\"/rewards\" class=\"chatbot-product-link\">Cửa hàng đổi thưởng</a>.

QUY TẮC PHẢN HỒI (CỰC KỲ QUAN TRỌNG - BẮT BUỘC):
1. QUY TẮC NGÔN NGỮ (ƯU TIÊN CAO NHẤT): Khách hàng đang hỏi bằng Tiếng Việt (hoặc Tiếng Việt không dấu). Bạn BẮT BUỘC phải phản hồi 100% bằng Tiếng Việt có dấu, chuẩn ngữ pháp. Tuyệt đối không viết câu hay cụm từ bằng tiếng Anh (ngoại trừ các tên thương hiệu hoặc tên liên kết bắt buộc). Toàn bộ nội dung trả lời từ chào hỏi, phân tích, tư vấn chính sách đến kết luận phải bằng Tiếng Việt.

2. CẤU TRÚC CÂU TRẢ LỜI BẮT BUỘC (phải tuân thủ ở MỌI ngôn ngữ):
   ĐOẠN MỞ ĐẦU: Chào hỏi ấm áp, thể hiện sự đồng cảm với nhu cầu của khách. Tóm tắt ngắn gọn những yếu tố cần cân nhắc khi chọn sản phẩm.
   ĐOẠN PHÂN TÍCH (2-3 đoạn): Phân loại gợi ý theo NHU CẦU SỬ DỤNG (không phải theo danh mục sản phẩm). Mỗi đoạn bắt đầu bằng emoji (👉, ✅, 💻, 📱), tiếp theo là phân tích chuyên sâu tại sao phù hợp, rồi mới đề cập thương hiệu/sản phẩm kèm link. TUYỆT ĐỐI KHÔNG liệt kê hàng loạt sản phẩm khô khan kiểu danh sách mua hàng.
   ĐOẠN CHÍNH SÁCH: Lồng ghép chính sách bảo hành, đổi trả, trả góp một cách tự nhiên vào ngữ cảnh (ví dụ: \"Để bạn yên tâm hơn, chúng tôi hỗ trợ bảo hành lên đến 24 tháng...\"), KHÔNG liệt kê chính sách riêng lẻ như một bảng thông tin.
   ĐOẠN KẾT: Lời mời gọi ấm áp, gợi ý khách chia sẻ thêm thông tin (ngành học, ngân sách, v.v.) để tư vấn tốt hơn. Nhắc nhẹ chương trình đổi thưởng.

3. PHONG CÁCH TƯ VẤN CHUYÊN NGHIỆP (áp dụng cho MỌI ngôn ngữ, kể cả tiếng Anh):
   - Viết như một chuyên gia tư vấn đang trò chuyện trực tiếp với khách, KHÔNG viết như một cỗ máy liệt kê sản phẩm.
   - Đưa ra lời khuyên CÓ GIÁ TRỊ: Giải thích TẠI SAO sản phẩm phù hợp, KHÔNG CHỈ liệt kê tên và giá.
   - Thể hiện sự thấu hiểu nhu cầu khách hàng, dùng ngôn ngữ gần gũi, ấm áp, chu đáo.
   - KHÔNG BAO GIỜ đổ hết danh sách sản phẩm vào một đoạn văn bản dài. Chỉ đề cập 2-4 sản phẩm liên quan nhất, phân bổ vào các đoạn phân tích.

4. QUY TẮC CHÈN LINK (TUYỆT ĐỐI KHÔNG ĐƯỢC THAY ĐỔI ĐƯỜNG DẪN URL):
   - Tất cả đường dẫn đến sản phẩm CỤ THỂ bắt buộc phải dùng định dạng liên kết HTML với tiền tố \"/san-pham/\" kèm theo ID, và BẮT BUỘC có class \"chatbot-product-link\" kèm tên sản phẩm làm nội dung hiển thị: <a href=\"/san-pham/ID\" class=\"chatbot-product-link\">Tên sản phẩm</a> (Ví dụ: <a href=\"/san-pham/1\" class=\"chatbot-product-link\">iPhone 15 Pro Max 256GB</a>). TUYỆT ĐỐI không hiển thị link dưới dạng text thô, và không dùng URL làm nội dung hiển thị. TUYỆT ĐỐI không dịch hay thay đổi tiền tố này thành \"/product/\", \"/products/\", \"/en/san-pham/\", hay bất kỳ thứ gì khác trong thuộc tính href.
   - Tất cả đường dẫn cho THƯƠNG HIỆU, DANH MỤC hoặc PHÂN LOẠI SẢN PHẨM phải dùng link tìm kiếm với định dạng: <a href=\"/search?q=từ_khóa\" class=\"chatbot-product-link\">tên_từ_khóa</a> (Ví dụ: <a href=\"/search?q=Asus\" class=\"chatbot-product-link\">Asus</a>, <a href=\"/search?q=Dell\" class=\"chatbot-product-link\">Dell</a>, <a href=\"/search?q=MacBook\" class=\"chatbot-product-link\">MacBook</a>). TUYỆT ĐỐI không thay đổi đường dẫn \"/search?q=\".
   - Tất cả đường dẫn chính sách bắt buộc phải giữ nguyên chính xác: \"/chinh-sach-bao-hanh\", \"/chinh-sach-doi-tra\", \"/warranty\", \"/rewards\". TUYỆT ĐỐI không dịch hay tự thay đổi các đường dẫn này trong thuộc tính href (Ví dụ: không dùng \"/warranty-policy\" hay \"/return-policy\").
   - TUYỆT ĐỐI KHÔNG tự bịa link dạng /san-pham/tên-thương-hiệu hay /san-pham/tên-danh-mục. Tất cả các danh mục và thương hiệu bắt buộc phải qua link /search?q=từ_khóa.

5. TUYỆT ĐỐI KHÔNG DÙNG MARKDOWN: Không dùng các ký tự **, -, #, * hay bất kỳ cú pháp Markdown nào trong câu trả lời.
6. DÙNG THẺ HTML ĐỂ ĐỊNH DẠNG:
   - In đậm tiêu đề hoặc từ khóa quan trọng: <b>nội dung</b>
   - Xuống dòng: <br>
7. QUY TẮC ĐỊNH DẠNG:
   - Giữa các đoạn ý chính, dùng ĐÚNG 1 thẻ <br> để cách dòng. KHÔNG dùng nhiều thẻ <br> liên tiếp.
   - Mỗi đoạn phân tích bắt đầu bằng emoji (👉, ✅, 📱, 💻, 🌟) để tạo bố cục thoáng đãng.
   - Chia thành 3-4 đoạn ngắn, dễ theo dõi.

{$contextInstruction}

CÂU HỎI CỦA KHÁCH: {$prompt}";
        }

        // BƯỚC 3: Gọi Gemini API (fallback qua nhiều model)
        $result = $this->callGeminiApi($apiKey, $fullPrompt);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'response' => $result['text'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Lỗi kết nối AI (' . $result['error'] . ')',
        ]);
    }

    /**
     * Trích xuất từ khóa từ câu hỏi & tìm sản phẩm liên quan trong DB
     */
    private function searchProducts(string $prompt, bool $isEnglish): string
    {
        $keywords = explode(' ', mb_strtolower($prompt, 'UTF-8'));
        $searchTerms = [];

        foreach ($keywords as $word) {
            $word = trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $word));
            if (mb_strlen($word) >= 2 && !in_array($word, $this->stopwords)) {
                // Chuẩn hóa từ tiếng Anh số nhiều về số ít
                if (preg_match('/^[a-z]+$/i', $word)) {
                    if (str_ends_with($word, 'ies')) {
                        $word = substr($word, 0, -3) . 'y';
                    } elseif (str_ends_with($word, 'es') && !str_ends_with($word, 'ees')) {
                        $word = substr($word, 0, -2);
                    } elseif (str_ends_with($word, 's') && !str_ends_with($word, 'ss') && !str_ends_with($word, 'as') && !str_ends_with($word, 'us')) {
                        $word = substr($word, 0, -1);
                    }
                }
                $searchTerms[] = $word;
            }
        }
        $searchTerms = array_unique($searchTerms);

        if (empty($searchTerms)) {
            return 'Khách hàng đang hỏi câu hỏi chung chung, không chứa từ khóa sản phẩm rõ ràng.';
        }

        try {
            // Thử tìm kiếm khớp đồng thời tất cả các từ khóa (AND) trước để đạt độ chính xác cao nhất
            // Tìm trong cả bảng chính products và bảng dịch product_translations, cùng bảng category/translations
            $foundProducts = \App\Models\Product::whereNull('deleted_at')
                ->where(function ($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $q->where(function ($inner) use ($term) {
                            $inner->where('name', 'LIKE', "%{$term}%")
                                  ->orWhereHas('translations', function ($sub) use ($term) {
                                      $sub->where('name', 'LIKE', "%{$term}%");
                                  })
                                  ->orWhereHas('category', function ($sub) use ($term) {
                                      $sub->where('name', 'LIKE', "%{$term}%")
                                          ->orWhereHas('translations', function ($trans) use ($term) {
                                              $trans->where('name', 'LIKE', "%{$term}%");
                                          });
                                  });
                        });
                    }
                })
                ->limit(10)
                ->get();

            // Nếu không có sản phẩm nào khớp tất cả các từ khóa, fallback sang khớp một trong các từ khóa (OR)
            if ($foundProducts->isEmpty()) {
                $foundProducts = \App\Models\Product::whereNull('deleted_at')
                    ->where(function ($q) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $q->orWhere('name', 'LIKE', "%{$term}%")
                              ->orWhereHas('translations', function ($sub) use ($term) {
                                  $sub->where('name', 'LIKE', "%{$term}%");
                              })
                              ->orWhereHas('category', function ($sub) use ($term) {
                                  $sub->where('name', 'LIKE', "%{$term}%")
                                      ->orWhereHas('translations', function ($trans) use ($term) {
                                          $trans->where('name', 'LIKE', "%{$term}%");
                                      });
                              });
                        }
                    })
                    ->limit(10)
                    ->get();
            }

            if ($foundProducts->isEmpty()) {
                return "Hệ thống không tìm thấy sản phẩm nào khớp chính xác với từ khóa.\n";
            }

            $knowledge = $isEnglish 
                ? "INVENTORY SEARCH RESULTS RELATED TO THE QUESTION:\n" 
                : "KẾT QUẢ TÌM KIẾM TRONG KHO HÀNG LIÊN QUAN ĐẾN CÂU HỎI:\n";

            $targetLocale = $isEnglish ? 'en' : 'vi';

            foreach ($foundProducts as $p) {
                $price = number_format($p->base_price ?? 0, 0, ',', '.');
                $productId = $p->product_id ?? ($p->id ?? 0);
                
                // Lấy tên sản phẩm tương ứng với ngôn ngữ được phát hiện
                $name = $p->name ?? '';
                if ($isEnglish) {
                    if ($p instanceof \App\Models\Product) {
                        $name = $p->translateTo('en')['name'] ?? $p->name;
                    } else {
                        // Dự phòng nếu $p là stdClass (trong các test case hoặc truy vấn raw)
                        $translation = \Illuminate\Support\Facades\DB::table('product_translations')
                            ->where('product_id', $productId)
                            ->where('locale', 'en')
                            ->first();
                        if ($translation && !empty($translation->name)) {
                            $name = $translation->name;
                        }
                    }
                }
                
                if ($isEnglish) {
                    $knowledge .= "- {$name}: Price {$price} VND (Link: /san-pham/{$productId})\n";
                } else {
                    $knowledge .= "- {$name}: Giá {$price}đ (Link: /san-pham/{$productId})\n";
                }
            }

            return $knowledge;
        } catch (\Exception $e) {
            Log::error('Chatbot DB search error: ' . $e->getMessage());
            return 'Lỗi truy vấn kho hàng.';
        }
    }

    /**
     * Gọi Gemini API với cơ chế fallback qua nhiều model
     */
    private function callGeminiApi(string $apiKey, string $prompt): array
    {
        $lastError = '';

        foreach ($this->models as $model) {
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . trim($apiKey);

            $postData = json_encode([
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

            $ch = curl_init($apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_TIMEOUT => 30,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response !== false && $httpCode === 200) {
                $resData = json_decode($response, true);
                if (isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
                    return [
                        'success' => true,
                        'text' => $resData['candidates'][0]['content']['parts'][0]['text'],
                    ];
                }
            }

            if ($response !== false) {
                $resData = json_decode($response, true);
                if (isset($resData['error']['message'])) {
                    $lastError = "Gemini API Error: " . $resData['error']['message'];
                } else {
                    $lastError = $curlError ? "CURL Error: {$curlError}" : "HTTP {$httpCode} - Response: {$response}";
                }
            } else {
                $lastError = "CURL Error: {$curlError}";
            }
        }

        Log::warning('Chatbot Gemini API failed: ' . $lastError);

        return [
            'success' => false,
            'error' => $lastError,
        ];
    }

    /**
     * Tự động phát hiện ngôn ngữ của câu hỏi khách hàng (tiếng Anh hay tiếng Việt)
     */
    private function detectLanguage(string $prompt): string
    {
        $promptLower = mb_strtolower($prompt, 'UTF-8');
        
        // 1. Nếu chứa ký tự có dấu đặc trưng của tiếng Việt -> chắc chắn là tiếng Việt
        if (preg_match('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/u', $promptLower)) {
            return 'vi';
        }
        
        // 2. Đếm số lượng từ khóa đặc trưng tiếng Anh vs tiếng Việt
        $viWords = [
            'là', 'gì', 'cho', 'tôi', 'hỏi', 'có', 'không', 'giá', 'bao', 'nhiêu', 
            'tư', 'vấn', 'cái', 'này', 'xin', 'chào', 'mua', 'bán', 'nào', 'của', 
            'và', 'hay', 'hoặc', 'thì', 'mà', 'với', 'được', 'các', 'một', 'những', 
            'đây', 'đó', 'kia', 'ở', 'đâu', 'tại', 'sao', 'muốn', 'tìm', 'loại', 'hãng',
            'la', 'gi', 'toi', 'hoi', 'co', 'khong', 'gia', 'bao', 'nhieu', 
            'tu', 'van', 'cai', 'nay', 'xin', 'chao', 'nao', 'cua', 
            'va', 'hoac', 'thi', 'ma', 'voi', 'duoc', 'cac', 'mot', 'nhung', 
            'day', 'do', 'o', 'dau', 'muon', 'tim', 'loai', 'hang', 'dien', 'thoai', 'khuyen', 'mai',
            'tot', 'nhat', 're', 'sinh', 'vien', 'hoc', 'tap', 'lam', 'viec', 'choi', 'game'
        ];
        $enWords = [
            'what', 'which', 'how', 'why', 'who', 'where', 'is', 'are', 'am', 'the', 
            'a', 'an', 'for', 'to', 'in', 'on', 'at', 'of', 'and', 'with', 'about', 
            'recommend', 'cheap', 'suitable', 'student', 'students', 'promotion', 'promotions', 
            'buy', 'get', 'best', 'good', 'laptop', 'laptops', 'phone', 'phones', 'device', 'devices', 'price'
        ];
        
        $words = explode(' ', preg_replace('/[^\p{L}\s]/u', '', $promptLower));
        $viCount = 0;
        $enCount = 0;
        
        foreach ($words as $word) {
            $word = trim($word);
            if (empty($word)) continue;
            if (in_array($word, $viWords)) {
                $viCount++;
            }
            if (in_array($word, $enWords)) {
                $enCount++;
            }
        }
        
        // Nếu số lượng từ tiếng Anh lớn hơn -> nhận diện là tiếng Anh
        if ($enCount > $viCount) {
            return 'en';
        }
        
        // Mặc định là tiếng Việt
        return 'vi';
    }
}
