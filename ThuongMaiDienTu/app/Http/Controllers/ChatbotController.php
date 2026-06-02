<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ChatbotController - Bộ điều khiển API Chatbot RAG (Retrieval-Augmented Generation)
 *
 * Nhiệm vụ chính:
 * 1. Tiếp nhận câu hỏi và ngữ cảnh (sản phẩm đang xem) từ người dùng ở Frontend.
 * 2. Phân tích ngôn ngữ câu hỏi (Tiếng Việt hay Tiếng Anh).
 * 3. Trích xuất từ khóa tìm kiếm, thực hiện truy vấn cơ sở dữ liệu (Database) để lấy thông tin các sản phẩm liên quan (RAG).
 * 4. Kết hợp câu hỏi, thông tin sản phẩm tìm được, và các chính sách của hệ thống (Bảo hành, Đổi trả, Trả góp, Tích điểm) thành một Prompt hướng dẫn chi tiết.
 * 5. Gửi Prompt này lên API Gemini của Google để AI tổng hợp câu trả lời thông minh, tự động chuyển đổi qua lại giữa nhiều model dự phòng nếu gặp lỗi (Fallback).
 * 6. Trả kết quả tư vấn đã được định dạng HTML sạch về cho giao diện Chatbot ở Frontend.
 */
class ChatbotController extends Controller
{
    /**
     * Danh sách các phiên bản mô hình AI Gemini để chạy dự phòng.
     * Nếu mô hình đầu tiên bị lỗi (quá tải, hết hạn ngạch...), hệ thống sẽ tự động thử các mô hình tiếp theo.
     */
    private array $models = [
        'gemini-3.1-flash-lite',
        'gemini-3.5-flash',
        'gemini-3-flash-preview',
        'gemini-2.5-flash',
    ];

    /**
     * Danh sách các từ vô nghĩa (Stop Words) trong tiếng Việt và tiếng Anh.
     * Những từ này sẽ bị loại bỏ khỏi câu hỏi khi trích xuất từ khóa tìm kiếm sản phẩm
     * để tránh việc tìm kiếm bị nhiễu và tăng tốc độ truy vấn cơ sở dữ liệu.
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
     * Phương thức chat(): Điểm nhận request từ Frontend gửi lên qua AJAX POST.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chat(Request $request)
    {
        // Lấy câu hỏi từ người dùng và làm sạch khoảng trắng thừa ở hai đầu
        $prompt = trim($request->input('prompt', ''));
        // Ngữ cảnh sản phẩm khách hàng đang xem (nếu khách đang ở trang chi tiết sản phẩm)
        $currentProductContext = trim($request->input('context', ''));
        // Số lượng tin nhắn đã gửi trong phiên chat này
        $messageCount = (int)$request->input('message_count', 0);

        // Kiểm tra nếu câu hỏi trống thì phản hồi yêu cầu người dùng nhập lại
        if (!$prompt) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng nhập câu hỏi.',
            ]);
        }

        // Lấy cấu hình khóa API Gemini từ file môi trường .env
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa cấu hình API Key cho AI.',
            ]);
        }

        // Tự động phát hiện ngôn ngữ dựa trên câu hỏi của khách hàng (tiếng Anh hay tiếng Việt)
        $detectedLang = $this->detectLanguage($prompt);
        $isEnglish = ($detectedLang === 'en');

        // BƯỚC 1: TRÍCH XUẤT THÔNG TIN SẢN PHẨM (RAG)
        // Tìm kiếm các sản phẩm trong kho hàng liên quan đến câu hỏi của khách
        $productKnowledge = $this->searchProducts($prompt, $isEnglish);

        // TỰ ĐỘNG PHÁT HÀNH MÃ GIẢM GIÁ (DYNAMIC COUPONING)
        $couponInstruction = '';
        if ($messageCount >= 5 && !session()->has('chatbot_coupon_code')) {
            $couponCode = 'CHAT5_' . strtoupper(\Illuminate\Support\Str::random(5));
            try {
                \App\Models\CouponFlashSale::create([
                    'promo_type' => 'Coupon',
                    'code' => $couponCode,
                    'discount_type' => 'percentage',
                    'discount_val' => 5,
                    'start_time' => \Illuminate\Support\Carbon::now(),
                    'end_time' => \Illuminate\Support\Carbon::now()->addDays(7),
                ]);
                session()->put('chatbot_coupon_code', $couponCode);
            } catch (\Exception $e) {
                Log::error('Lỗi tạo chatbot coupon: ' . $e->getMessage());
            }
        }

        if (session()->has('chatbot_coupon_code')) {
            $code = session('chatbot_coupon_code');
            if ($isEnglish) {
                $couponInstruction = "\nSPECIAL PROMOTION INCENTIVE: The system has generated an exclusive 5% discount coupon code for this customer: `{$code}`. If they ask about discount codes, promotions, prices, or seem hesitant to buy, you MUST offer them this coupon code. Encourage them to apply it at checkout to get 5% off.";
            } else {
                $couponInstruction = "\nƯU ĐÃI KHUYẾN MÃI ĐẶC BIỆT: Hệ thống đã tạo một mã giảm giá 5% độc quyền cho khách hàng này: `{$code}`. Nếu khách hàng hỏi về mã giảm giá, chương trình khuyến mãi, giá cả, hoặc có vẻ do dự chưa muốn mua, bạn BẮT BUỘC phải tặng cho họ mã giảm giá này. Hãy khuyên họ áp dụng mã này khi thanh toán để được giảm giá 5%.";
            }
        }

        // TỰ ĐỘNG TẠO PHIẾU SỬA CHỮA (REPAIR BOOKING)
        if ($isEnglish) {
            $repairInstruction = "\nREPAIR BOOKING RULE: If the customer mentions wanting to book a repair appointment, schedule a repair, or reports a device issue they want fixed, you must guide them politely and extract: customer_name, customer_phone, customer_email, issue_desc, schedule_date, and imei_serial. At the VERY END of your response, you MUST append a command block in the exact format: `[[CREATE_REPAIR_TICKET:{\"customer_name\":\"...\",\"customer_phone\":\"...\",\"customer_email\":\"...\",\"issue_desc\":\"...\",\"schedule_date\":\"YYYY-MM-DD HH:MM:SS\",\"imei_serial\":\"...\"}]]`. Use the current system local time " . \Illuminate\Support\Carbon::now()->toIso8601String() . " as reference to calculate dates (e.g. 'tomorrow at 9am' or 'next Monday'). If any field is not provided, use null or fallback value (e.g., fallback customer details to the logged in user details if available, or default name to 'Khách hàng qua Chat', phone/email to 'N/A', imei_serial to 'N/A'). DO NOT mention this tag or JSON structure to the customer, just append it silently.";
        } else {
            $repairInstruction = "\nQUY TẮC ĐẶT LỊCH SỬA CHỮA: Nếu khách hàng đề cập đến việc muốn đặt lịch sửa chữa, hẹn giờ sửa, hoặc thông báo thiết bị bị hỏng cần sửa, bạn phải tư vấn lịch sự và tự động trích xuất: customer_name (tên khách), customer_phone (sđt), customer_email (email), issue_desc (mô tả lỗi), schedule_date (ngày giờ hẹn), và imei_serial (số IMEI/Serial). Tại CUỐI CÙNG của câu trả lời, bạn Bắt Buộc phải chèn một khối lệnh theo đúng định dạng: `[[CREATE_REPAIR_TICKET:{\"customer_name\":\"...\",\"customer_phone\":\"...\",\"customer_email\":\"...\",\"issue_desc\":\"...\",\"schedule_date\":\"YYYY-MM-DD HH:MM:SS\",\"imei_serial\":\"...\"}]]`. Hãy dựa vào thời gian hệ thống hiện tại là " . \Illuminate\Support\Carbon::now()->toIso8601String() . " để tính toán ngày giờ cụ thể (ví dụ 'ngày mai lúc 9h sáng'). Nếu thông tin nào thiếu, hãy dùng giá trị mặc định (tên khách hàng mặc định lấy tên của user đăng nhập hoặc 'Khách hàng qua Chat', phone/email mặc định 'N/A', imei_serial mặc định 'N/A'). KHÔNG giải thích về thẻ này cho khách hàng biết, chỉ âm thầm chèn nó ở cuối cùng câu trả lời.";
        }

        // BƯỚC 2: CHUẨN BỊ NỘI DUNG PROMPT & CÁC CHỈ THỊ CHO AI
        if ($isEnglish) {
            // Định nghĩa chỉ thị ngữ cảnh sản phẩm hiện tại bằng tiếng Anh
            $contextInstruction = 'The customer is on the Homepage or browsing general categories. Please provide a general consultation.';
            if ($currentProductContext) {
                $contextInstruction = "SPECIAL NOTICE: The customer is VIEWING THIS PRODUCT:\n{$currentProductContext}\n-> Prioritize using this product's details in your response.";
            }
            $contextInstruction .= $couponInstruction . $repairInstruction;

            // Xây dựng Prompt tiếng Anh toàn diện gửi lên cho AI
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
            // Định nghĩa chỉ thị ngữ cảnh sản phẩm hiện tại bằng tiếng Việt
            $contextInstruction = 'Khách hàng đang ở Trang chủ hoặc xem danh mục chung. Hãy tư vấn tổng quan.';
            if ($currentProductContext) {
                $contextInstruction = "ĐẶC BIỆT LƯU Ý: Khách hàng ĐANG XEM SẢN PHẨM NÀY:\n{$currentProductContext}\n-> Ưu tiên dùng thông tin sản phẩm này để trả lời.";
            }
            $contextInstruction .= $couponInstruction . $repairInstruction;

            // Xây dựng Prompt tiếng Việt toàn diện gửi lên cho AI
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

        // BƯỚC 3: GỬI YÊU CẦU LÊN API GEMINI ĐỂ NHẬN PHẢN HỒI (Hỗ trợ tự động dự phòng model)
        $result = $this->callGeminiApi($apiKey, $fullPrompt);

        // Trả kết quả JSON về cho Frontend dựa vào trạng thái gọi AI thành công hay thất bại
        if ($result['success']) {
            $text = $result['text'];

            // Xử lý tạo Phiếu Sửa Chữa nếu có thẻ đặc biệt
            if (preg_match('/\[\[CREATE_REPAIR_TICKET:(.*?)\]\]/s', $text, $matches)) {
                $jsonData = trim($matches[1]);
                $ticketData = json_decode($jsonData, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($ticketData)) {
                    try {
                        // Xác định các thông tin cơ bản
                        $custName = $ticketData['customer_name'] ?? null;
                        if (empty($custName) || $custName === 'null') {
                            $custName = auth()->check() ? auth()->user()->name : 'Khách hàng qua Chat';
                        }

                        $custPhone = $ticketData['customer_phone'] ?? null;
                        if (empty($custPhone) || $custPhone === 'null') {
                            $custPhone = auth()->check() ? auth()->user()->phone : 'N/A';
                        }

                        $custEmail = $ticketData['customer_email'] ?? null;
                        if (empty($custEmail) || $custEmail === 'null') {
                            $custEmail = auth()->check() ? auth()->user()->email : 'N/A';
                        }

                        $issue = $ticketData['issue_desc'] ?? 'Lỗi thiết bị (Tạo qua Chatbot)';

                        $schedDate = null;
                        if (!empty($ticketData['schedule_date']) && $ticketData['schedule_date'] !== 'null') {
                            try {
                                $schedDate = \Illuminate\Support\Carbon::parse($ticketData['schedule_date']);
                            } catch (\Exception $ex) {
                                $schedDate = \Illuminate\Support\Carbon::now()->addDay();
                            }
                        } else {
                            $schedDate = \Illuminate\Support\Carbon::now()->addDay();
                        }

                        $imei = $ticketData['imei_serial'] ?? null;
                        if (empty($imei) || $imei === 'null') {
                            $imei = 'N/A';
                        }

                        $ticket = \App\Models\RepairTicket::create([
                            'user_id' => auth()->id(),
                            'customer_name' => $custName,
                            'customer_phone' => $custPhone,
                            'customer_email' => $custEmail,
                            'customer_address' => $ticketData['customer_address'] ?? 'N/A',
                            'issue_desc' => $issue,
                            'schedule_date' => $schedDate,
                            'imei_serial' => $imei,
                            'status' => 'Received',
                        ]);

                        // Xóa thẻ khỏi câu trả lời của AI
                        $text = str_replace($matches[0], '', $text);

                        // Thêm thông báo xác nhận đẹp mắt vào phản hồi
                        $formattedDate = $schedDate->format('H:i d/m/Y');
                        if ($isEnglish) {
                            $text .= "<br><br><b>📅 Booking Confirmation:</b> We have created a draft repair ticket <b>#{$ticket->ticket_id}</b> for you. Schedule: {$formattedDate}.";
                        } else {
                            $text .= "<br><br><b>📅 Xác nhận đặt lịch:</b> Hệ thống đã tự động tạo bản nháp phiếu sửa chữa <b>#{$ticket->ticket_id}</b> dành cho bạn. Lịch hẹn: {$formattedDate}.";
                        }
                    } catch (\Exception $ex) {
                        Log::error('Lỗi khi lưu phiếu sửa chữa từ Chatbot: ' . $ex->getMessage());
                    }
                }
            }

            return response()->json([
                'success' => true,
                'response' => $text,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Lỗi kết nối AI (' . $result['error'] . ')',
        ]);
    }

    /**
     * Trích xuất từ khóa từ câu hỏi người dùng & tìm kiếm sản phẩm liên quan trong Database.
     * Đây là quá trình "Retrieval" của kỹ thuật RAG.
     * 
     * @param string $prompt Câu hỏi thô từ khách hàng
     * @param bool $isEnglish Cờ báo hiệu câu hỏi bằng tiếng Anh
     * @return string Trả về chuỗi danh sách sản phẩm tìm được để AI tham khảo làm ngữ cảnh
     */
    private function searchProducts(string $prompt, bool $isEnglish): string
    {
        // Tách câu hỏi thành mảng các từ đơn bằng khoảng trắng
        $keywords = explode(' ', mb_strtolower($prompt, 'UTF-8'));
        $searchTerms = [];

        // Duyệt qua từng từ để lọc và chuẩn hóa từ khóa
        foreach ($keywords as $word) {
            // Loại bỏ các ký tự đặc biệt, dấu câu, chỉ giữ lại chữ cái và chữ số
            $word = trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $word));
            
            // Chỉ giữ lại từ có độ dài từ 2 ký tự trở lên và KHÔNG nằm trong danh sách từ vô nghĩa (stopwords)
            if (mb_strlen($word) >= 2 && !in_array($word, $this->stopwords)) {
                // CHUẨN HÓA TIẾNG ANH: Nếu là từ tiếng Anh, tự động chuyển danh từ số nhiều về số ít để khớp database dễ hơn
                if (preg_match('/^[a-z]+$/i', $word)) {
                    if (str_ends_with($word, 'ies')) {
                        $word = substr($word, 0, -3) . 'y'; // Ví dụ: properties -> property
                    } elseif (str_ends_with($word, 'es') && !str_ends_with($word, 'ees')) {
                        $word = substr($word, 0, -2); // Ví dụ: boxes -> box
                    } elseif (str_ends_with($word, 's') && !str_ends_with($word, 'ss') && !str_ends_with($word, 'as') && !str_ends_with($word, 'us')) {
                        $word = substr($word, 0, -1); // Ví dụ: laptops -> laptop
                    }
                }
                $searchTerms[] = $word;
            }
        }
        
        // Loại bỏ các từ khóa bị trùng lặp
        $searchTerms = array_unique($searchTerms);

        // Nếu sau khi lọc không còn từ khóa chất lượng nào, trả về thông báo để AI tự tư vấn chung
        if (empty($searchTerms)) {
            return 'Khách hàng đang hỏi câu hỏi chung chung, không chứa từ khóa sản phẩm rõ ràng.';
        }

        try {
            // CHIẾN LƯỢC TRUY VẤN:
            // Lần 1: Thử tìm kiếm khớp ĐỒNG THỜI tất cả các từ khóa (toán tử AND).
            // Tìm kiếm trong cột Tên sản phẩm chính, Bản dịch sản phẩm tiếng Anh, Danh mục sản phẩm, và Bản dịch danh mục.
            $foundProducts = \App\Models\Product::with(['variants.inventoryItems'])->whereNull('deleted_at')
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

            // Lần 2 (Fallback): Nếu không có sản phẩm nào khớp đồng thời tất cả các từ,
            // nới lỏng điều kiện bằng cách tìm sản phẩm khớp ÍT NHẤT MỘT trong các từ khóa (toán tử OR).
            if ($foundProducts->isEmpty()) {
                $foundProducts = \App\Models\Product::with(['variants.inventoryItems'])->whereNull('deleted_at')
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

            // Nếu vẫn không tìm thấy sản phẩm nào trong database
            if ($foundProducts->isEmpty()) {
                return "Hệ thống không tìm thấy sản phẩm nào khớp chính xác với từ khóa.\n";
            }

            // Xây dựng chuỗi văn bản danh sách sản phẩm để nối vào Prompt gửi AI
            $knowledge = $isEnglish 
                ? "INVENTORY SEARCH RESULTS RELATED TO THE QUESTION:\n" 
                : "KẾT QUẢ TÌM KIẾM TRONG KHO HÀNG LIÊN QUAN ĐẾN CÂU HỎI:\n";

            foreach ($foundProducts as $p) {
                $price = number_format($p->base_price ?? 0, 0, ',', '.');
                $productId = $p->product_id ?? ($p->id ?? 0);
                
                // Lấy tên sản phẩm tương ứng với ngôn ngữ đã xác định
                $name = $p->name ?? '';
                if ($isEnglish) {
                    if ($p instanceof \App\Models\Product) {
                        // Tải bản dịch tên sản phẩm tiếng Anh
                        $name = $p->translateTo('en')['name'] ?? $p->name;
                    } else {
                        // Dự phòng nếu $p là đối tượng stdClass trong truy vấn raw
                        $translation = \Illuminate\Support\Facades\DB::table('product_translations')
                            ->where('product_id', $productId)
                            ->where('locale', 'en')
                            ->first();
                        if ($translation && !empty($translation->name)) {
                            $name = $translation->name;
                        }
                    }
                }

                // Lấy chi tiết các biến thể (Màu sắc, Dung lượng, Hàng tồn kho)
                $variantsInfo = [];
                if ($p instanceof \App\Models\Product) {
                    foreach ($p->variants as $var) {
                        $stockCount = $var->in_stock_count;
                        $colorStr = $var->color ?: ($isEnglish ? 'Default Color' : 'Màu mặc định');
                        $romStr = $var->rom_capacity ? " - {$var->rom_capacity}" : "";
                        if ($isEnglish) {
                            $variantsInfo[] = "{$colorStr}{$romStr}: {$stockCount} in stock";
                        } else {
                            $variantsInfo[] = "Màu {$colorStr}{$romStr}: còn {$stockCount} sản phẩm";
                        }
                    }
                }
                $variantsStr = !empty($variantsInfo) ? ($isEnglish ? " [Available variants: " : " [Phiên bản có sẵn: ") . implode(', ', $variantsInfo) . "]" : "";
                
                // Ghép nối thông tin dạng: Tên sản phẩm - Giá - Link để AI dựng câu trả lời
                if ($isEnglish) {
                    $knowledge .= "- {$name}: Price {$price} VND{$variantsStr} (Link: /san-pham/{$productId})\n";
                } else {
                    $knowledge .= "- {$name}: Giá {$price}đ{$variantsStr} (Link: /san-pham/{$productId})\n";
                }
            }

            return $knowledge;
        } catch (\Exception $e) {
            // Ghi nhận lỗi truy vấn database nếu xảy ra
            Log::error('Lỗi tìm kiếm sản phẩm cho Chatbot: ' . $e->getMessage());
            return 'Lỗi truy vấn kho hàng.';
        }
    }

    /**
     * Thực hiện gửi yêu cầu POST HTTP tới API của Google Gemini.
     * Tự động thử qua nhiều model trong mảng $models nếu gặp lỗi.
     * 
     * @param string $apiKey Khóa API Gemini
     * @param string $prompt Nội dung chỉ thị gửi AI
     * @return array Mảng chứa kết quả thành công và text, hoặc báo lỗi cụ thể
     */
    private function callGeminiApi(string $apiKey, string $prompt): array
    {
        $lastError = '';

        // Duyệt qua từng phiên bản mô hình AI (Cơ chế fallback)
        foreach ($this->models as $model) {
            // URL endpoint của API Gemini theo từng model tương ứng
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . trim($apiKey);

            // Cấu trúc dữ liệu Payload JSON chuẩn yêu cầu bởi Gemini
            $postData = json_encode([
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]);

            // Khởi tạo tiến trình CURL để gửi request
            $ch = curl_init($apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_SSL_VERIFYPEER => false, // Bỏ qua kiểm tra chứng chỉ SSL cục bộ để tránh lỗi môi trường local
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_TIMEOUT => 30, // Thời gian chờ tối đa 30 giây
            ]);

            // Thực thi gửi request và ghi nhận phản hồi
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Nếu kết nối CURL không lỗi và máy chủ Google trả về mã HTTP 200 (Thành công)
            if ($response !== false && $httpCode === 200) {
                $resData = json_decode($response, true);
                // Trích xuất đoạn text trả về từ cấu trúc JSON của Gemini
                if (isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
                    return [
                        'success' => true,
                        'text' => $resData['candidates'][0]['content']['parts'][0]['text'],
                    ];
                }
            }

            // Ghi nhận thông tin lỗi của model hiện tại để chuẩn bị chuyển qua model tiếp theo
            if ($response !== false) {
                $resData = json_decode($response, true);
                if (isset($resData['error']['message'])) {
                    $lastError = "Gemini API Error ({$model}): " . $resData['error']['message'];
                } else {
                    $lastError = $curlError ? "CURL Error: {$curlError}" : "HTTP {$httpCode} (Model: {$model}) - Response: {$response}";
                }
            } else {
                $lastError = "CURL Error: {$curlError} (Model: {$model})";
            }
        }

        // Ghi lại cảnh báo vào log nếu tất cả các mô hình Gemini đều thất bại
        Log::warning('Toàn bộ các model Gemini API của Chatbot đều thất bại: ' . $lastError);

        return [
            'success' => false,
            'error' => $lastError,
        ];
    }

    /**
     * Tự động phân tích và phát hiện câu hỏi của khách hàng đang được viết bằng tiếng Anh hay tiếng Việt.
     * 
     * @param string $prompt Câu hỏi thô
     * @return string 'vi' nếu là tiếng Việt, 'en' nếu là tiếng Anh
     */
    private function detectLanguage(string $prompt): string
    {
        $promptLower = mb_strtolower($prompt, 'UTF-8');
        
        // CÁCH 1: KIỂM TRA DẤU TIẾNG VIỆT
        // Nếu chuỗi chứa các ký tự nguyên âm có dấu đặc trưng của tiếng Việt -> 100% là tiếng Việt
        if (preg_match('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/u', $promptLower)) {
            return 'vi';
        }
        
        // CÁCH 2: TÍNH TẦN SUẤT TỪ KHÓA (Cho trường hợp tiếng Việt không có dấu)
        // Mảng các từ viết không dấu hoặc có dấu cơ bản hay gặp trong tiếng Việt
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
        // Mảng các từ thông dụng hay gặp trong tiếng Anh
        $enWords = [
            'what', 'which', 'how', 'why', 'who', 'where', 'is', 'are', 'am', 'the', 
            'a', 'an', 'for', 'to', 'in', 'on', 'at', 'of', 'and', 'with', 'about', 
            'recommend', 'cheap', 'suitable', 'student', 'students', 'promotion', 'promotions', 
            'buy', 'get', 'best', 'good', 'laptop', 'laptops', 'phone', 'phones', 'device', 'devices', 'price'
        ];
        
        // Tách câu thành các từ đơn và lọc bỏ ký tự đặc biệt
        $words = explode(' ', preg_replace('/[^\p{L}\s]/u', '', $promptLower));
        $viCount = 0;
        $enCount = 0;
        
        foreach ($words as $word) {
            $word = trim($word);
            if (empty($word)) continue;
            // Tăng biến đếm nếu khớp từ trong danh mục tương ứng
            if (in_array($word, $viWords)) {
                $viCount++;
            }
            if (in_array($word, $enWords)) {
                $enCount++;
            }
        }
        
        // Nếu số lượng từ tiếng Anh xuất hiện nhiều hơn hẳn tiếng Việt
        if ($enCount > $viCount) {
            return 'en';
        }
        
        // Mặc định phản hồi bằng tiếng Việt
        return 'vi';
    }
}
