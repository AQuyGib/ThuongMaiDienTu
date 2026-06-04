<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Installment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Services\InstallmentAIService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ==========================================
 * BỘ ĐIỀU HƯỚNG: InstallmentController (Trang Khách Hàng)
 * Ý NGHĨA NGHIỆP VỤ: Quản lý quy trình đăng ký hồ sơ mua trả góp từ phía khách hàng tại trang chi tiết sản phẩm.
 * 
 * - Giúp khách hàng đăng ký mua sản phẩm theo hình thức trả góp trực tuyến (chọn công ty tài chính, Kredivo, hoặc thẻ tín dụng).
 * - Tự động hóa các khâu: tính toán số tiền trả trước, số tiền vay, số tiền phải trả mỗi tháng, trợ giá "Thu cũ đổi mới" (nếu có),
 *   tự động tạo đơn hàng, trừ sản phẩm khỏi kho, chuyển hồ sơ qua AI thẩm định, và gửi thông báo cho khách hàng cũng như Admin.
 * ==========================================
 */
class InstallmentController extends Controller
{
    protected $aiService;
    protected $notificationService;

    /**
     * Khởi tạo bộ điều hướng với các dịch vụ bổ trợ:
     * - InstallmentAIService: Thẩm định hồ sơ bằng AI.
     * - NotificationService: Tạo và gửi thông báo trong hệ thống.
     */
    public function __construct(InstallmentAIService $aiService, NotificationService $notificationService)
    {
        $this->aiService = $aiService;
        $this->notificationService = $notificationService;
    }

    /**
     * ==========================================
     * HÀM: register (Request $request)
     * Ý NGHĨA NGHIỆP VỤ: Tiếp nhận và xử lý yêu cầu đăng ký mua trả góp của khách hàng.
     * 
     * 1. Kiểm tra đăng nhập: Khách hàng bắt buộc phải đăng nhập tài khoản trước khi mua trả góp.
     * 2. Kiểm tra tính hợp lệ của thông tin gửi lên (Validation):
     *    - Phương thức trả góp (financial_company: công ty tài chính, credit_card: thẻ tín dụng, kredivo).
     *    - Đối tác tương ứng với từng phương thức (Shinhan Finance, Home Credit, Vietcombank Credit Card, v.v.).
     *    - Kỳ hạn vay phù hợp (3, 6, 9, 12 tháng).
     *    - Thông tin cá nhân khách hàng (họ tên, số điện thoại, số CCCD).
     * 3. Tính toán trợ giá "Thu cũ đổi mới" (Trade-In):
     *    - Nếu khách hàng tham gia, hệ thống sẽ trợ giá giảm thêm 10% giá trị sản phẩm (giới hạn tối đa 2 triệu đồng) và trừ trực tiếp vào giá bán sản phẩm.
     * 4. Tính toán tài chính:
     *    - Tính toán số tiền khách hàng trả trước (ví dụ 30% đối với công ty tài chính, 0% đối với thẻ tín dụng).
     *    - Tính toán số tiền vay còn lại, lãi suất tháng, phí dịch vụ.
     *    - Tính số tiền trả mỗi tháng và tổng số tiền phải trả thực tế.
     * 5. Lưu trữ vào cơ sở dữ liệu bằng cơ chế giao dịch an toàn (DB Transaction):
     *    - Tạo mới một đơn hàng tạm ở trạng thái "Chờ duyệt" (Pending).
     *    - Tìm thiết bị (IMEI) còn trong kho để giữ hàng cho khách, chuyển trạng thái thiết bị sang "Đã bán" (Sold) để tránh trùng lặp.
     *    - Tạo hợp đồng trả góp (Installment) với mã hợp đồng chuẩn hóa: `TGO-YYMMDD-[Random Hash]` (ví dụ: TGO-260604-A1B2C3).
     * 6. Phân tích rủi ro hồ sơ bằng AI: Gửi dữ liệu hợp đồng sang Gemini AI để tự động đánh giá mức độ tin cậy và chấm điểm tín dụng.
     * 7. Gửi thông báo đến tài khoản khách hàng và đẩy thông tin đến giao diện quản trị của Admin.
     * ==========================================
     */
    public function register(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng đăng nhập trước khi đăng ký trả góp.'
            ], 401);
        }

        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,product_id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,variant_id'],
            'method' => ['required', 'in:financial_company,credit_card,kredivo'],
            'partner' => [
                'required', 
                'string', 
                'max:100',
                function ($attribute, $value, $fail) use ($request) {
                    $method = $request->input('method');
                    if ($method === 'financial_company') {
                        $allowed = ['Shinhan Finance', 'Home Credit', 'HD Saison', 'Mirae Asset'];
                        if (!in_array($value, $allowed)) {
                            $fail('Đối tác tài chính không hợp lệ.');
                        }
                    } elseif ($method === 'kredivo') {
                        if ($value !== 'Kredivo') {
                            $fail('Đối tác Kredivo không hợp lệ.');
                        }
                    } elseif ($method === 'credit_card') {
                        $allowedBanks = ['Vietcombank', 'Techcombank', 'MB Bank', 'Sacombank', 'VPBank'];
                        $allowedPartners = array_map(fn($b) => $b . ' Credit Card', $allowedBanks);
                        if (!in_array($value, $allowedPartners)) {
                            $fail('Ngân hàng trả góp không hợp lệ.');
                        }
                    }
                }
            ],
            'period' => [
                'required', 
                'integer', 
                function ($attribute, $value, $fail) use ($request) {
                    $method = $request->input('method');
                    if ($method === 'financial_company') {
                        if (!in_array($value, [3, 4, 6, 9, 12])) {
                            $fail('Kỳ hạn trả góp công ty tài chính không hợp lệ.');
                        }
                    } elseif ($method === 'kredivo') {
                        if (!in_array($value, [3, 6, 12])) {
                            $fail('Kỳ hạn Kredivo không hợp lệ.');
                        }
                    } elseif ($method === 'credit_card') {
                        if (!in_array($value, [3, 6, 9, 12])) {
                            $fail('Kỳ hạn thẻ tín dụng không hợp lệ.');
                        }
                    }
                }
            ],
            'customer_name' => ['required', 'string', 'min:2', 'max:150'],
            'customer_phone' => ['required', 'string', 'regex:/^0[0-9]{8,9}$/'],
            'customer_id_card' => ['required_if:method,financial_company', 'nullable', 'string', 'regex:/^[0-9]{12}$/'],
            'trade_in' => ['nullable', 'boolean'],
            'shipping_address' => ['nullable', 'string', 'max:250'],
        ]);

        $productId = (int) $request->input('product_id');
        $variantId = $request->input('variant_id');
        $method = $request->input('method');
        $partner = $request->input('partner');
        $period = (int) $request->input('period');
        $custName = strip_tags($request->input('customer_name'));
        $custPhone = strip_tags($request->input('customer_phone'));
        $custIdCard = strip_tags($request->input('customer_id_card'));
        $tradeIn = (bool) $request->input('trade_in', false);
        $shippingAddress = strip_tags($request->input('shipping_address') ?: (Auth::user()->address ?: 'Nhận tại cửa hàng'));

        // Lấy sản phẩm và kiểm tra giá
        $product = Product::findOrFail($productId);
        
        // Nếu có chọn variant, tìm variant, nếu không lấy variant đầu tiên
        $variant = null;
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
        } else {
            $variant = ProductVariant::where('product_id', $productId)->first();
        }

        if (!$variant) {
            $variant = ProductVariant::create([
                'product_id' => $productId,
                'color' => 'Mặc định',
                'stock' => 99,
            ]);
        }

        $productPrice = (int) ($variant ? $variant->total_price : $product->base_price);

        // ==========================================
        // CHỨC NĂNG: TÍNH TOÁN TRỢ GIÁ "THU CŨ ĐỔI MỚI" (TRADE-IN)
        // ------------------------------------------
        // Khi khách hàng đổi điện thoại/thiết bị cũ để nâng cấp lên sản phẩm mới, cửa hàng sẽ tặng 
        // một khoản tiền giảm giá đặc biệt (trợ giá) để trừ thẳng vào giá bán khi làm hồ sơ trả góp.
        //
        // CÁCH HOẠT ĐỘNG:
        // - Khách hàng sẽ được giảm giá 10% của giá bán sản phẩm.
        // - Số tiền giảm này bị giới hạn tối đa là 2.000.000đ (dù máy có đắt đến mấy thì trợ giá tối đa là 2 triệu).
        // - Đối với phụ kiện giá rẻ (ví dụ cáp sạc 2 triệu), hệ thống tự giảm 10% tức là 200.000đ,
        //   giúp giá bán sau giảm vẫn hợp lý (còn lại 1,8 triệu) chứ không bị đưa về 0đ (miễn phí) hoặc bị âm tiền.
        // ==========================================
        
        // Kiểm tra xem khách hàng có tham gia chương trình Thu cũ đổi mới hay không (biến $tradeIn nhận giá trị boolean từ request)
        if ($tradeIn) {
            // Tính số tiền trợ giá thu cũ đổi mới bằng 10% đơn giá hiện tại của sản phẩm
            $tenPercentOfPrice = (int)round($productPrice * 0.1);
            // Giới hạn mức trợ giá thu cũ đổi mới tối đa là 2.000.000 VNĐ
            $tradeInDiscount = min($tenPercentOfPrice, 2000000);
            // Khấu trừ số tiền trợ giá vừa tính được trực tiếp vào đơn giá sản phẩm ban đầu
            $discountedPrice = $productPrice - $tradeInDiscount;
            // Đảm bảo đơn giá sản phẩm sau khi trừ trợ giá không bao giờ bị âm hoặc nhỏ hơn 0đ
            $productPrice = max(0, $discountedPrice);
        }

        // Tính toán các chi phí phía server để đảm bảo tính an toàn (Server-side calculation)
        $prepayAmount = 0;
        $interestRate = 0;
        $serviceFee = 0;

        if ($method === 'financial_company') {
            $prepayAmount = (int) round($productPrice * 0.3); // Trả trước 30% mặc định
            if ($partner === 'Home Credit') {
                $interestRate = 0.01;
                $serviceFee = 50000;
            } elseif ($partner === 'HD Saison') {
                $interestRate = 0.015;
                $serviceFee = 60000;
            } elseif ($partner === 'Mirae Asset') {
                $interestRate = 0.02;
                $serviceFee = 70000;
            } // Shinhan Finance mặc định lãi suất 0% và 0đ phí
        } elseif ($method === 'kredivo') {
            // Kredivo trả trước 0%
            $prepayAmount = 0;
            if ($period === 3) {
                $interestRate = 0.0;
                $serviceFee = 0;
            } else {
                $interestRate = 0.025; // Các kỳ hạn khác lãi suất 2.5%
                $serviceFee = 30000;
            }
        } else {
            // Credit Card trả trước 0%
            $prepayAmount = 0;
            $interestRate = 0.0; // 0% lãi suất trả góp thẻ tín dụng
            $serviceFee = 20000; // Phí chuyển đổi giao dịch thẻ
        }

        $loanAmount = $productPrice - $prepayAmount;
        $monthlyNoInterest = $loanAmount / $period;
        $monthlyInterest = $loanAmount * $interestRate;
        $monthlyPayment = (int) round($monthlyNoInterest + $monthlyInterest + $serviceFee);
        $totalPayment = $prepayAmount + ($monthlyPayment * $period);
        $diffAmount = $totalPayment - $productPrice;

        try {
            $installment = DB::transaction(function () use (
                $product, $variant, $productPrice, $prepayAmount, $loanAmount, $monthlyPayment,
                $interestRate, $serviceFee, $totalPayment, $diffAmount, $method, $partner,
                $period, $custName, $custPhone, $custIdCard, $tradeIn, $shippingAddress
            ) {
                // 1. Tạo đơn hàng (order)
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'order_type' => 'Online',
                    'total_amount' => $productPrice,
                    'shipping_fee' => 0,
                    'discount_amount' => 0,
                    'wallet_points_used' => 0,
                    'final_amount' => $productPrice,
                    'payment_method' => 'Installment',
                    'status' => 'Pending',
                    'customer_name' => $custName,
                    'customer_phone' => $custPhone,
                    'shipping_address' => $shippingAddress,
                    'note' => 'Đăng ký trả góp qua ' . $partner . ' kỳ hạn ' . $period . ' tháng. ' . ($tradeIn ? '[Thu cũ lên đời]' : ''),
                    'order_code' => 'ORD' . now()->format('YmdHis') . random_int(100, 999),
                ]);

                // 2. Tìm hoặc tạo mới Inventory Item ở trạng thái In_Stock để bán
                $invItem = InventoryItem::where('variant_id', $variant->variant_id)
                    ->where('status', 'In_Stock')
                    ->first();

                if (!$invItem) {
                    $invItem = InventoryItem::create([
                        'variant_id' => $variant->variant_id,
                        'po_id' => PurchaseOrder::first()?->po_id ?? 1,
                        'imei_serial' => 'TG-' . strtoupper(Str::random(12)),
                        'warehouse_loc' => 'Kho Online',
                        'status' => 'In_Stock',
                    ]);
                }

                // Tạo OrderDetail và cập nhật kho
                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'item_id' => $invItem->item_id,
                    'price' => $productPrice,
                ]);

                $invItem->status = 'Sold';
                $invItem->save();

                // 3. Tạo hợp đồng trả góp (Installment) theo chuẩn chuyên nghiệp: TGO-[YYMMDD]-[6 ký tự ngẫu nhiên]
                $installmentCode = 'TGO-' . now()->format('ymd') . '-' . strtoupper(Str::random(6));
                $installment = Installment::create([
                    'order_id' => $order->order_id,
                    'installment_code' => $installmentCode,
                    'method' => $method,
                    'partner' => $partner,
                    'period' => $period,
                    'product_price' => $productPrice,
                    'prepay_amount' => $prepayAmount,
                    'loan_amount' => $loanAmount,
                    'monthly_payment' => $monthlyPayment,
                    'interest_rate' => $interestRate,
                    'service_fee' => $serviceFee,
                    'total_payment' => $totalPayment,
                    'difference_amount' => $diffAmount > 0 ? $diffAmount : 0,
                    'customer_name' => $custName,
                    'customer_phone' => $custPhone,
                    'customer_id_card' => $custIdCard,
                    'trade_in' => $tradeIn,
                    'status' => 'Pending_Approval',
                ]);

                return $installment;
            });

            // 4. Chạy AI phân tích rủi ro hồ sơ (Gemini AI Credit Risk Assessment)
            try {
                $this->aiService->analyzeInstallment($installment);
            } catch (\Throwable $ae) {
                Log::error('Lỗi chạy phân tích AI cho hợp đồng trả góp: ' . $ae->getMessage());
            }

            // 5. Gửi thông báo tới Admin và Khách hàng
            $this->sendNotifications($installment);

            return response()->json([
                'status' => 'success',
                'installment_code' => $installment->installment_code,
                'message' => 'Đăng ký trả góp thành công! Hồ sơ đang được hệ thống thẩm định.'
            ]);

        } catch (\Throwable $e) {
            Log::error('Lỗi đăng ký trả góp: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi xử lý đăng ký trả góp: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ==========================================
     * HÀM: sendNotifications (Installment $installment)
     * Ý NGHĨA NGHIỆP VỤ: Gửi thông báo tự động cho khách hàng và Admin khi có hồ sơ đăng ký trả góp mới.
     * 
     * 1. Lấy thông tin tài khoản khách hàng hiện tại và đối tác tài chính (ví dụ: Home Credit, Kredivo).
     * 2. Gửi một thông báo cá nhân đến tài khoản của khách hàng để họ biết hồ sơ đã được tiếp nhận thành công và đang chờ duyệt.
     * 3. Tìm toàn bộ các tài khoản Admin, Quản lý, Nhân viên trong hệ thống (role_id = 1, 2, 4) và gửi thông báo chuông/ứng dụng
     *    để họ truy cập vào trang quản trị duyệt hồ sơ này kịp thời.
     * ==========================================
     */
    private function sendNotifications(Installment $installment)
    {
        $user = Auth::user();
        $partnerLabel = $installment->partner;
        
        // Thông báo cho khách hàng
        $this->notificationService->createForUser($user, [
            'type' => 'installment.created',
            'title' => 'Đăng ký trả góp đã được gửi',
            'content' => 'Hồ sơ trả góp #' . $installment->installment_code . ' qua đối tác ' . $partnerLabel . ' của bạn đã được tiếp nhận và đang chờ xét duyệt.',
            'action_url' => url('/orders'),
            'data' => [
                'installment_code' => $installment->installment_code,
                'status' => $installment->status,
            ],
        ]);

        // Thông báo cho tất cả admin
        $admins = \App\Models\User::whereIn('role_id', [1, 2, 4])->get();
        foreach ($admins as $admin) {
            $this->notificationService->createForUser($admin, [
                'type' => 'admin.installment.created',
                'title' => 'Hồ sơ trả góp mới cần duyệt',
                'content' => 'Khách hàng ' . $installment->customer_name . ' vừa đăng ký gói trả góp #' . $installment->installment_code . ' (' . $partnerLabel . ').',
                'action_url' => url('/admin/installments/' . $installment->id),
                'data' => [
                    'installment_id' => $installment->id,
                    'installment_code' => $installment->installment_code,
                ],
            ]);
        }
    }
}
