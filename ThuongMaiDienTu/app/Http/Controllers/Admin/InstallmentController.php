<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Installment;
use App\Models\InstallmentPayment;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\InventoryItem;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InstallmentController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Danh sách hồ sơ trả góp
     */
    public function index(Request $request)
    {
        $query = Installment::with(['order.user']);

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Lọc theo đối tác
        if ($request->filled('partner')) {
            $query->where('partner', 'like', '%' . $request->input('partner') . '%');
        }

        // Lọc theo AI risk level
        if ($request->filled('ai_risk_level')) {
            $query->where('ai_risk_level', $request->input('ai_risk_level'));
        }

        // Tìm kiếm theo tên/sđt/mã hồ sơ
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('installment_code', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $search . '%');
            });
        }

        $installments = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.installments.index', compact('installments'));
    }

    /**
     * Chi tiết hồ sơ trả góp
     */
    public function show($id)
    {
        $installment = Installment::with(['order.details.inventoryItem.variant.product', 'payments'])->findOrFail($id);
        return view('admin.installments.show', compact('installment'));
    }

    /**
     * Tạo mới hợp đồng trả góp trực tiếp tại quầy (POS)
     */
    public function create()
    {
        $products = \App\Models\Product::orderBy('name')->get();
        // Lấy tất cả biến thể kèm label
        $variants = \App\Models\ProductVariant::with('product')->get();
        $users = \App\Models\User::orderBy('full_name')->get();
        
        return view('admin.installments.create', compact('products', 'variants', 'users'));
    }

    /**
     * Lưu hợp đồng trả góp tạo mới từ admin
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => ['nullable', 'integer', 'exists:users,user_id'],
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
            'prepay_amount' => ['required', 'integer', 'min:0'],
            'customer_name' => ['required', 'string', 'min:2', 'max:150'],
            'customer_phone' => ['required', 'string', 'regex:/^0[0-9]{8,9}$/'],
            'customer_id_card' => ['required_if:method,financial_company', 'nullable', 'string', 'regex:/^[0-9]{12}$/'],
            'trade_in' => ['nullable', 'boolean'],
            'shipping_address' => ['required', 'string', 'max:250'],
        ]);

        $userId = $request->input('user_id') ? (int) $request->input('user_id') : null;
        $productId = (int) $request->input('product_id');
        $variantId = $request->input('variant_id');
        $method = $request->input('method');
        $partner = $request->input('partner');
        $period = (int) $request->input('period');
        $prepayInput = (int) $request->input('prepay_amount');
        $custName = strip_tags($request->input('customer_name'));
        $custPhone = strip_tags($request->input('customer_phone'));
        $custIdCard = strip_tags($request->input('customer_id_card'));
        $tradeIn = (bool) $request->input('trade_in', false);
        $shippingAddress = strip_tags($request->input('shipping_address'));

        $product = \App\Models\Product::findOrFail($productId);
        $variant = null;
        if ($variantId) {
            $variant = \App\Models\ProductVariant::find($variantId);
        } else {
            $variant = \App\Models\ProductVariant::where('product_id', $productId)->first();
        }

        if (!$variant) {
            $variant = \App\Models\ProductVariant::create([
                'product_id' => $productId,
                'color' => 'Mặc định',
                'stock' => 99,
            ]);
        }

        $productPrice = (int) ($variant ? $variant->total_price : $product->base_price);

        // ==========================================
        // CHỨC NĂNG: TÍNH TOÁN TRỢ GIÁ "THU CŨ ĐỔI MỚI" (TRADE-IN)
        // ------------------------------------------
        // [DÀNH CHO NGƯỜI KHÔNG BIẾT CODE - Ý NGHĨA CHỨC NĂNG]:
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

        // Server-side calculation to ensure consistency
        $interestRate = 0;
        $serviceFee = 0;

        if ($method === 'financial_company') {
            if ($partner === 'Home Credit') {
                $interestRate = 0.01;
                $serviceFee = 50000;
            } elseif ($partner === 'HD Saison') {
                $interestRate = 0.015;
                $serviceFee = 60000;
            } elseif ($partner === 'Mirae Asset') {
                $interestRate = 0.02;
                $serviceFee = 70000;
            }
        } elseif ($method === 'kredivo') {
            if ($period !== 3) {
                $interestRate = 0.025;
                $serviceFee = 30000;
            }
        } else {
            $serviceFee = 20000;
        }

        $prepayAmount = $prepayInput;
        $loanAmount = $productPrice - $prepayAmount;
        if ($loanAmount < 0) {
            return redirect()->back()->withInput()->with('error', 'Số tiền trả trước không thể lớn hơn giá trị sản phẩm.');
        }

        $monthlyNoInterest = $loanAmount / $period;
        $monthlyInterest = $loanAmount * $interestRate;
        $monthlyPayment = (int) round($monthlyNoInterest + $monthlyInterest + $serviceFee);
        $totalPayment = $prepayAmount + ($monthlyPayment * $period);
        $diffAmount = $totalPayment - $productPrice;

        try {
            $installment = DB::transaction(function () use (
                $userId, $product, $variant, $productPrice, $prepayAmount, $loanAmount, $monthlyPayment,
                $interestRate, $serviceFee, $totalPayment, $diffAmount, $method, $partner,
                $period, $custName, $custPhone, $custIdCard, $tradeIn, $shippingAddress
            ) {
                // 1. Tạo đơn hàng
                $order = Order::create([
                    'user_id' => $userId,
                    'order_type' => 'POS',
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
                    'note' => 'Hợp đồng trả góp tại quầy qua ' . $partner . ' kỳ hạn ' . $period . ' tháng.',
                    'order_code' => 'POS' . now()->format('YmdHis') . random_int(100, 999),
                ]);

                // 2. Tìm hoặc tạo mới Inventory Item
                $invItem = InventoryItem::where('variant_id', $variant->variant_id)
                    ->where('status', 'In_Stock')
                    ->first();

                if (!$invItem) {
                    $invItem = InventoryItem::create([
                        'variant_id' => $variant->variant_id,
                        'po_id' => \App\Models\PurchaseOrder::first()?->po_id ?? 1,
                        'imei_serial' => 'TG-POS-' . strtoupper(Str::random(10)),
                        'warehouse_loc' => 'Cửa hàng',
                        'status' => 'In_Stock',
                    ]);
                    $variant->increment('stock');
                }

                // Phòng hờ bất đồng bộ dữ liệu: Đảm bảo tồn kho biến thể ít nhất là 1 để trừ kho thành công
                if ($variant->stock < 1) {
                    $variant->stock = 1;
                    $variant->save();
                }

                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'item_id' => $invItem->item_id,
                    'price' => $productPrice,
                ]);

                // 3. Tạo mã hợp đồng trả góp theo chuẩn bán lẻ chuyên nghiệp: TGP-HCM-[YYMMDD]-[5 ký tự ngẫu nhiên]
                $installmentCode = 'TGP-HCM-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));

                // Cập nhật trạng thái sản phẩm kho thông qua dịch vụ để trừ tồn kho của biến thể
                app(\App\Services\InventoryService::class)->markInventoryItemSold($invItem, [
                    'order_id' => $order->order_id,
                    'reference_type' => 'order',
                    'reference_id' => $order->order_id,
                    'note' => 'Trừ kho bán hàng trả góp tại quầy cho hợp đồng #' . $installmentCode,
                ]);

                // 4. Tạo hợp đồng trả góp
                return Installment::create([
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
            });

            // Phân tích AI rủi ro
            try {
                $aiService = app(\App\Services\InstallmentAIService::class);
                $aiService->analyzeInstallment($installment);
            } catch (\Throwable $ae) {
                Log::error('Lỗi chạy phân tích AI cho hợp đồng POS: ' . $ae->getMessage());
            }

            return redirect()->route('admin.installments.show', $installment->id)
                ->with('success', 'Đã tạo hợp đồng trả góp tại quầy #' . $installment->installment_code . ' thành công.');

        } catch (\Throwable $e) {
            Log::error('Lỗi POS tạo trả góp: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Chỉnh sửa hợp đồng trả góp
     */
    public function edit($id)
    {
        $installment = Installment::with('order')->findOrFail($id);
        $products = \App\Models\Product::orderBy('name')->get();
        $variants = \App\Models\ProductVariant::with('product')->get();
        $users = \App\Models\User::orderBy('full_name')->get();

        return view('admin.installments.edit', compact('installment', 'products', 'variants', 'users'));
    }

    /**
     * Cập nhật hợp đồng trả góp
     */
    public function update(Request $request, $id)
    {
        $installment = Installment::findOrFail($id);

        $request->validate([
            'customer_name' => ['required', 'string', 'min:2', 'max:150'],
            'customer_phone' => ['required', 'string', 'regex:/^0[0-9]{8,9}$/'],
            'customer_id_card' => ['nullable', 'string', 'regex:/^[0-9]{12}$/'],
            'prepay_amount' => ['required', 'integer', 'min:0'],
            'shipping_address' => ['required', 'string', 'max:250'],
            'status' => ['required', 'in:Pending_Approval,Approved,Rejected'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $prepayInput = (int) $request->input('prepay_amount');
        $custName = strip_tags($request->input('customer_name'));
        $custPhone = strip_tags($request->input('customer_phone'));
        $custIdCard = strip_tags($request->input('customer_id_card'));
        $shippingAddress = strip_tags($request->input('shipping_address'));
        $status = $request->input('status');
        $notes = strip_tags($request->input('notes'));

        $oldStatus = $installment->status;

        // Bổ sung các ràng buộc nghiệp vụ:
        if ($oldStatus === 'Approved' && $status === 'Rejected') {
            return redirect()->back()->withInput()->with('error', 'Hợp đồng đã được duyệt và kích hoạt lịch thanh toán, không thể chuyển ngược thành Từ chối.');
        }

        if ($oldStatus === 'Approved' && $prepayInput !== (int) $installment->prepay_amount) {
            // Kiểm tra xem đã có kỳ thanh toán nào được trả chưa
            $paidCount = $installment->payments()->where('status', 'Paid')->count();
            if ($paidCount > 0) {
                return redirect()->back()->withInput()->with('error', 'Không thể sửa đổi số tiền trả trước khi khách hàng đã bắt đầu đóng tiền trả góp hàng tháng.');
            }
        }
        $productPrice = $installment->product_price;
        $loanAmount = $productPrice - $prepayInput;

        if ($loanAmount < 0) {
            return redirect()->back()->withInput()->with('error', 'Số tiền trả trước không thể lớn hơn giá trị sản phẩm.');
        }

        // Recalculate monthly payment based on interest
        $interestRate = $installment->interest_rate;
        $serviceFee = $installment->service_fee;
        $period = $installment->period;

        $monthlyNoInterest = $loanAmount / $period;
        $monthlyInterest = $loanAmount * $interestRate;
        $monthlyPayment = (int) round($monthlyNoInterest + $monthlyInterest + $serviceFee);
        $totalPayment = $prepayInput + ($monthlyPayment * $period);
        $diffAmount = $totalPayment - $productPrice;

        try {
            DB::transaction(function() use (
                $installment, $prepayInput, $loanAmount, $monthlyPayment, $totalPayment, $diffAmount,
                $custName, $custPhone, $custIdCard, $shippingAddress, $status, $notes, $oldStatus
            ) {
                // Update installment contract
                $installment->update([
                    'prepay_amount' => $prepayInput,
                    'loan_amount' => $loanAmount,
                    'monthly_payment' => $monthlyPayment,
                    'total_payment' => $totalPayment,
                    'difference_amount' => $diffAmount > 0 ? $diffAmount : 0,
                    'customer_name' => $custName,
                    'customer_phone' => $custPhone,
                    'customer_id_card' => $custIdCard,
                    'status' => $status,
                    'rejection_reason' => $notes,
                ]);

                // Nếu chuyển từ Chờ duyệt sang Đã duyệt
                if ($oldStatus === 'Pending_Approval' && $status === 'Approved') {
                    // Khởi tạo lịch thanh toán
                    $period = (int) $installment->period;
                    for ($i = 1; $i <= $period; $i++) {
                        InstallmentPayment::create([
                            'installment_id' => $installment->id,
                            'term_number' => $i,
                            'amount' => $monthlyPayment,
                            'due_date' => now()->addMonths($i)->startOfDay(),
                            'status' => 'Unpaid',
                        ]);
                    }

                    // Đồng bộ trạng thái đơn hàng
                    $order = $installment->order;
                    if ($order) {
                        $order->update([
                            'status' => 'Processing',
                            'payment_status' => 'paid',
                        ]);
                    }

                    // Ghi nhận Cashbook tiền cọc
                    if ($prepayInput > 0) {
                        \App\Models\Cashbook::create([
                            'type' => 'Income',
                            'amount' => $prepayInput,
                            'description' => 'Thu tiền trả trước hợp đồng trả góp #' . $installment->installment_code . ' - Khách hàng: ' . $installment->customer_name,
                            'reference_id' => $installment->order_id,
                        ]);
                    }
                }

                // Nếu hợp đồng ĐÃ duyệt từ trước, nhưng Admin sửa tiền trả trước (chưa đóng kỳ nào) -> Tính lại và tái tạo lịch thanh toán
                if ($oldStatus === 'Approved' && $prepayInput !== (int) $installment->prepay_amount) {
                    $installment->payments()->delete();
                    
                    $period = (int) $installment->period;
                    for ($i = 1; $i <= $period; $i++) {
                        InstallmentPayment::create([
                            'installment_id' => $installment->id,
                            'term_number' => $i,
                            'amount' => $monthlyPayment,
                            'due_date' => now()->addMonths($i)->startOfDay(),
                            'status' => 'Unpaid',
                        ]);
                    }
                }

                // Update associated order shipping details
                $order = $installment->order;
                if ($order) {
                    $order->update([
                        'customer_name' => $custName,
                        'customer_phone' => $custPhone,
                        'shipping_address' => $shippingAddress,
                    ]);
                }
            });

            // Gửi thông báo nếu có chuyển trạng thái
            if ($oldStatus === 'Pending_Approval' && $status === 'Approved') {
                try {
                    $order = $installment->order;
                    if ($order && $order->user) {
                        $this->notificationService->createForUser($order->user, [
                            'type' => 'installment.approved',
                            'title' => 'Hợp đồng trả góp được phê duyệt',
                            'content' => 'Hợp đồng trả góp #' . $installment->installment_code . ' đã được phê duyệt thành công. Lịch thanh toán đã được kích hoạt.',
                            'action_url' => url('/orders'),
                        ]);
                    }
                } catch (\Throwable $ne) {}
            } elseif ($oldStatus === 'Pending_Approval' && $status === 'Rejected') {
                try {
                    $order = $installment->order;
                    if ($order && $order->user) {
                        $this->notificationService->createForUser($order->user, [
                            'type' => 'installment.rejected',
                            'title' => 'Hợp đồng trả góp bị từ chối',
                            'content' => 'Hợp đồng trả góp #' . $installment->installment_code . ' đã bị từ chối. Lý do: ' . $notes,
                            'action_url' => url('/orders'),
                        ]);
                    }
                } catch (\Throwable $ne) {}
            }

            return redirect()->route('admin.installments.show', $installment->id)
                ->with('success', 'Đã cập nhật hợp đồng trả góp #' . $installment->installment_code . ' thành công.');

        } catch (\Throwable $e) {
            Log::error('Lỗi cập nhật trả góp: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Phê duyệt hồ sơ trả góp
     */
    public function approve($id)
    {
        $installment = Installment::findOrFail($id);

        if ($installment->status !== 'Pending_Approval') {
            return redirect()->back()->with('error', 'Hồ sơ này đã được xử lý từ trước.');
        }

        try {
            DB::transaction(function() use ($installment) {
                // 1. Cập nhật trạng thái hợp đồng trả góp
                $installment->update([
                    'status' => 'Approved',
                ]);

                // 2. Tạo lịch đóng tiền (Installment Payments Schedule)
                $period = (int) $installment->period;
                $monthlyAmount = $installment->monthly_payment;

                for ($i = 1; $i <= $period; $i++) {
                    InstallmentPayment::create([
                        'installment_id' => $installment->id,
                        'term_number' => $i,
                        'amount' => $monthlyAmount,
                        'due_date' => now()->addMonths($i)->startOfDay(),
                        'status' => 'Unpaid',
                    ]);
                }

                // 3. Cập nhật trạng thái đơn hàng tương ứng sang Processing
                $order = $installment->order;
                if ($order) {
                    $order->update([
                        'status' => 'Processing',
                        'payment_status' => 'paid', // Trả góp đã duyệt xem như thanh toán thành công
                    ]);
                }

                // 4. Ghi nhận tiền trả trước vào Sổ Quỹ (Cashbook) nếu có trả trước > 0
                if ($installment->prepay_amount > 0) {
                    \App\Models\Cashbook::create([
                        'type' => 'Income',
                        'amount' => $installment->prepay_amount,
                        'description' => 'Thu tiền trả trước hợp đồng trả góp #' . $installment->installment_code . ' - Khách hàng: ' . $installment->customer_name,
                        'reference_id' => $installment->order_id,
                    ]);
                }
            });

            // 4. Gửi thông báo cho khách hàng
            $order = $installment->order;
            if ($order && $order->user) {
                $this->notificationService->createForUser($order->user, [
                    'type' => 'installment.approved',
                    'title' => 'Hồ sơ trả góp được DUYỆT!',
                    'content' => 'Chúc mừng! Hồ sơ trả góp #' . $installment->installment_code . ' của bạn đã được phê duyệt thành công. Đơn hàng đang được chuẩn bị.',
                    'action_url' => url('/orders'),
                    'data' => [
                        'installment_id' => $installment->id,
                        'installment_code' => $installment->installment_code,
                    ]
                ]);
            }

            return redirect()->route('admin.installments.show', $installment->id)
                ->with('success', 'Đã phê duyệt hồ sơ trả góp #' . $installment->installment_code . ' và khởi tạo lịch thanh toán thành công.');

        } catch (\Throwable $e) {
            Log::error('Lỗi khi phê duyệt trả góp: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    /**
     * Từ chối hồ sơ trả góp
     */
    public function reject(Request $request, $id)
    {
        $installment = Installment::findOrFail($id);

        if ($installment->status !== 'Pending_Approval') {
            return redirect()->back()->with('error', 'Hồ sơ này đã được xử lý từ trước.');
        }

        $reason = $request->input('reject_reason') ?: 'Không đạt tiêu chí thẩm định tín dụng của hệ thống.';

        try {
            DB::transaction(function() use ($installment, $reason) {
                // 1. Cập nhật trạng thái hợp đồng trả góp
                $installment->update([
                    'status' => 'Rejected',
                    'rejection_reason' => $reason,
                ]);

                // 2. Cập nhật trạng thái đơn hàng sang Cancelled
                $order = $installment->order;
                if ($order) {
                    $order->update([
                        'status' => 'Cancelled',
                    ]);

                    // 3. Hoàn trả kho hàng (In_Stock) cho các sản phẩm trong đơn hàng
                    $orderDetails = $order->details;
                    foreach ($orderDetails as $detail) {
                        $itemId = $detail->item_id;
                        $inventoryItem = InventoryItem::find($itemId);
                        if ($inventoryItem) {
                            app(\App\Services\InventoryService::class)->restoreInventoryItem($inventoryItem, [
                                'order_id' => $order->order_id,
                                'reference_type' => 'order',
                                'reference_id' => $order->order_id,
                                'note' => 'Hoàn kho khi từ chối hồ sơ trả góp #' . $installment->installment_code,
                            ]);
                        }
                    }
                }
            });

            // 4. Gửi thông báo cho khách hàng
            $order = $installment->order;
            if ($order && $order->user) {
                $this->notificationService->createForUser($order->user, [
                    'type' => 'installment.rejected',
                    'title' => 'Hồ sơ trả góp bị từ chối',
                    'content' => 'Hồ sơ trả góp #' . $installment->installment_code . ' của bạn đã bị từ chối. Lý do: ' . $reason,
                    'action_url' => url('/orders'),
                    'data' => [
                        'installment_id' => $installment->id,
                        'installment_code' => $installment->installment_code,
                    ]
                ]);
            }

            return redirect()->route('admin.installments.show', $installment->id)
                ->with('success', 'Đã từ chối hồ sơ trả góp #' . $installment->installment_code . '.');

        } catch (\Throwable $e) {
            Log::error('Lỗi khi từ chối trả góp: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    /**
     * Xóa hợp đồng trả góp
     */
    public function destroy($id)
    {
        $installment = Installment::findOrFail($id);

        try {
            DB::transaction(function() use ($installment) {
                // Restore inventory if order exists
                $order = $installment->order;
                if ($order) {
                    $orderDetails = $order->details;
                    foreach ($orderDetails as $detail) {
                        $itemId = $detail->item_id;
                        $inventoryItem = InventoryItem::find($itemId);
                        if ($inventoryItem) {
                            app(\App\Services\InventoryService::class)->restoreInventoryItem($inventoryItem, [
                                'order_id' => $order->order_id,
                                'reference_type' => 'order',
                                'reference_id' => $order->order_id,
                                'note' => 'Hoàn kho khi xóa hợp đồng trả góp #' . $installment->installment_code,
                            ]);
                        }
                    }
                    // Delete order details
                    $order->details()->delete();
                    // Delete order
                    $order->delete();
                }

                // Delete payments schedule
                $installment->payments()->delete();
                // Delete installment
                $installment->delete();
            });

            return redirect()->route('admin.installments.index')
                ->with('success', 'Đã xóa hợp đồng trả góp và khôi phục kho hàng thành công.');

        } catch (\Throwable $e) {
            Log::error('Lỗi khi xóa hợp đồng: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    /**
     * Xuất hóa đơn trả trước (In hóa đơn / Phiếu thu)
     */
    public function printInvoice($id)
    {
        $installment = Installment::with(['order.details.inventoryItem.variant.product', 'order.user'])->findOrFail($id);
        return view('admin.installments.invoice', compact('installment'));
    }

    /**
     * Xác nhận thanh toán kỳ trả góp hàng tháng
     */
    public function payMonth($id)
    {
        $payment = InstallmentPayment::with('installment')->findOrFail($id);

        if ($payment->status === 'Paid') {
            return redirect()->back()->with('error', 'Kỳ trả góp này đã được đóng tiền trước đó.');
        }

        try {
            DB::transaction(function() use ($payment) {
                // 1. Cập nhật kỳ hạn thanh toán
                $payment->update([
                    'status' => 'Paid',
                    'payment_date' => now(),
                ]);

                // 2. Ghi nhận doanh thu vào Sổ Quỹ (Cashbook)
                \App\Models\Cashbook::create([
                    'type' => 'Income',
                    'amount' => $payment->amount,
                    'description' => 'Thu tiền trả góp định kỳ thứ ' . $payment->term_number . '/' . $payment->installment->period . ' - Hợp đồng: #' . $payment->installment->installment_code . ' - Khách hàng: ' . $payment->installment->customer_name,
                    'reference_id' => $payment->installment->order_id,
                ]);
            });

            return redirect()->back()->with('success', 'Đã ghi nhận thanh toán Kỳ thứ ' . $payment->term_number . ' và cập nhật Sổ Quỹ thành công.');
        } catch (\Throwable $e) {
            Log::error('Lỗi thanh toán kỳ trả góp: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
}
