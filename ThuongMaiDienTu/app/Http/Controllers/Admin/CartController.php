<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\InventoryItem;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\CouponFlashSale;
use Illuminate\Support\Str;
use App\Services\PointsService;
use App\Services\FlashSaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function __construct(private readonly FlashSaleService $flashSaleService)
    {
    }
    /**
     * Hàm: index
     * Công dụng: Hiển thị trang giỏ hàng của khách hàng (Frontend shopping cart).
     *            - Lấy danh sách sản phẩm hiện có trong session 'cart'.
     *            - Truy vấn thông tin sản phẩm từ DB để cập nhật giá mới nhất (ưu tiên giá Flash Sale nếu có).
     *            - Truyền dữ liệu giỏ hàng sang giao diện Blade.
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $cartItems = collect($cart)->map(function ($item, $id) {
            $product = Product::find($id);
            if (!$product) {
                return null;
            }
            $effectivePrice = isset($item['flash_sale_price']) ? (int) $item['flash_sale_price'] : (int) $product->base_price;
            return [
                'id' => $id,
                'name' => $product->name,
                'price' => $effectivePrice,
                'quantity' => $item['quantity'],
                'stock' => 10,
                'selected' => $item['selected'] ?? true,
                'image' => $product->thumbnail,
                'url' => route('product.show', $id),
                'category_id' => $product->category_id
            ];
        })->filter()->values();

        $recommendedProducts = collect();

        // ONLY query recommended products if the cart is NOT empty
        if ($cartItems->isNotEmpty()) {
            $cartProductIds = $cartItems->pluck('id')->toArray();
            $cartCategoryIds = $cartItems->pluck('category_id')->filter()->unique()->toArray();

            $query = Product::query()->whereNotIn('product_id', $cartProductIds);

            if (!empty($cartCategoryIds)) {
                $query->whereIn('category_id', $cartCategoryIds);
            }

            $recommendedProducts = $query->inRandomOrder()->limit(4)->get();

            // If not enough products, fetch more from other categories
            if ($recommendedProducts->count() < 4) {
                $remainingCount = 4 - $recommendedProducts->count();
                $moreProducts = Product::whereNotIn('product_id', array_merge($cartProductIds, $recommendedProducts->pluck('product_id')->toArray()))
                    ->inRandomOrder()
                    ->limit($remainingCount)
                    ->get();
                $recommendedProducts = $recommendedProducts->merge($moreProducts);
            }
        }

        return view('frontend.cart.shoppingcart', compact('cartItems', 'recommendedProducts'));
    }

    /**
     * Hàm: add
     * Công dụng: Thêm một sản phẩm (hoặc combo sản phẩm phụ kiện mua kèm) vào giỏ hàng session.
     * Logic đặc biệt:
     *   1. Kiểm tra chương trình Flash Sale đang hoạt động của sản phẩm để áp dụng giá Flash Sale và kiểm tra tồn kho.
     *   2. Xử lý bảo mật máy chủ (Server-side validation) cho combo mua kèm:
     *      - Nếu request gửi kèm tham số `parent_id` (ID của sản phẩm chính), kiểm tra xem sản phẩm phụ kiện này có
     *        nằm trong danh sách combo được cấu hình của sản phẩm chính trong DB hay không (`product_combos`).
     *      - Nếu khớp cấu hình, truy vấn loại giảm giá ('percentage' hoặc 'fixed') và giá trị giảm tương ứng từ cột pivot.
     *      - Áp dụng công thức giảm giá lên giá nền (giá bán thường hoặc giá Flash Sale) để tính ra giá thực tế sau giảm.
     *      - Giá trị này được gán trực tiếp vào thuộc tính `price` và `flash_sale_price` trong giỏ hàng để tránh việc người
     *        dùng can thiệp và sửa giá từ Client-side.
     *   3. Trả về phản hồi JSON (nếu là AJAX) hoặc redirect về trang giỏ hàng (nếu là nút Mua Ngay).
     */
    public function add(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $quantity = (int) $request->input('quantity', 1);
        $product = Product::findOrFail($productId);
        $flashSaleProduct = $this->flashSaleService->getFlashSaleProductFor($product);

        $cart = session()->get('cart', []);
        $existingQuantity = (int) ($cart[$productId]['quantity'] ?? 0);
        $newQuantity = $existingQuantity + $quantity;

        $salePrice = null;
        if ($flashSaleProduct && $flashSaleProduct->is_active) {
            $remaining = $this->flashSaleService->getRemainingQuantity($flashSaleProduct);
            if ($newQuantity > $remaining) {
                return back()->with('error', 'Số lượng Flash Sale còn lại không đủ.');
            }
            $salePrice = (int) $flashSaleProduct->sale_price;
        }

        // Kiểm tra xem sản phẩm có được mua kèm theo combo của sản phẩm chính không
        $parentProductId = $request->input('parent_id');
        if ($parentProductId) {
            $parentProduct = Product::find($parentProductId);
            if ($parentProduct) {
                // Truy vấn bảng trung gian product_combos để lấy thông tin cấu hình giảm giá
                $comboRelation = $parentProduct->comboProducts()->where('product_combos.combo_product_id', $productId)->first();
                if ($comboRelation) {
                    $pivot = $comboRelation->pivot;
                    $basePriceToDiscount = $salePrice ?? (int) $product->base_price;
                    
                    // Áp dụng giảm giá theo phần trăm (%) hoặc số tiền cố định (đ)
                    if ($pivot->discount_type === 'percentage') {
                        $salePrice = (int) ($basePriceToDiscount * (1 - $pivot->discount_value / 100));
                    } else {
                        $salePrice = (int) ($basePriceToDiscount - $pivot->discount_value);
                    }
                    
                    if ($salePrice < 0) {
                        $salePrice = 0;
                    }
                }
            }
        }

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $newQuantity;
            $cart[$productId]['price'] = $salePrice ?? (int) $product->base_price;
            $cart[$productId]['flash_sale_price'] = $salePrice;
            $cart[$productId]['selected'] = true;
        } else {
            $cart[$productId] = [
                'name' => $product->name,
                'quantity' => $quantity,
                'price' => $salePrice ?? (int) $product->base_price,
                'flash_sale_price' => $salePrice,
                'image' => $product->thumbnail,
                'selected' => true,
            ];
        }

        session()->put('cart', $cart);

        if ($request->has('buy_now')) {
            return redirect()->route('cart.index')->with('success', 'Đã thêm vào giỏ hàng!');
        }

        return response()->json(['status' => 'success', 'cart_count' => $this->getCartTotalQuantity($cart)]);
    }

    public function shipping()
    {
        $cart = session()->get('cart', []);
        $cartItems = collect($cart)->map(function ($item, $id) {
            $product = Product::find($id);
            if (!$product) return null;
            return [
                'id' => $id,
                'name' => $product->name,
                'price' => (int) $product->base_price,
                'quantity' => $item['quantity'],
                'stock' => 10,
                'selected' => true,
                'image' => $product->thumbnail,
                'url' => route('product.show', $id)
            ];
        })->filter()->values();

        return view('frontend.cart.ShippingCosts', compact('cartItems'));
    }

    public function checkout()
    {
        return view('frontend.cart.pay');
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng.
     */
    public function update(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $quantity = (int) $request->input('quantity');

        if ($quantity < 1) {
            return response()->json(['status' => 'error', 'message' => 'Số lượng không hợp lệ.'], 400);
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['status' => 'error', 'message' => 'Sản phẩm không tồn tại.'], 404);
        }

        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            $flashSaleProduct = $this->flashSaleService->getFlashSaleProductFor($product);
            if ($flashSaleProduct && $flashSaleProduct->is_active) {
                $remaining = $this->flashSaleService->getRemainingQuantity($flashSaleProduct);
                if ($quantity > $remaining) {
                    return response()->json([
                        'status' => 'error', 
                        'message' => "Số lượng Flash Sale tối đa có thể mua là $remaining."
                    ], 400);
                }
            }

            $cart[$productId]['quantity'] = $quantity;
            session()->put('cart', $cart);

            return response()->json([
                'status' => 'success',
                'cart_count' => $this->getCartTotalQuantity($cart),
                'message' => 'Cập nhật số lượng thành công.'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Sản phẩm không có trong giỏ hàng.'], 404);
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng.
     */
    public function remove(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
            return response()->json([
                'status' => 'success',
                'cart_count' => $this->getCartTotalQuantity($cart),
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Sản phẩm không có trong giỏ hàng.'], 404);
    }

    /**
     * Xóa sạch giỏ hàng.
     */
    public function clear()
    {
        session()->forget('cart');
        return response()->json([
            'status' => 'success',
            'cart_count' => 0,
            'message' => 'Đã làm trống giỏ hàng.'
        ]);
    }

    /**
     * Bật/tắt chọn sản phẩm để thanh toán.
     */
    public function toggleSelect(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $selected = (bool) $request->input('selected', true);
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['selected'] = $selected;
            session()->put('cart', $cart);
            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật trạng thái chọn thành công.'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Sản phẩm không có trong giỏ hàng.'], 404);
    }

    /**
     * Bật/tắt chọn tất cả sản phẩm.
     */
    public function toggleAll(Request $request)
    {
        $selected = (bool) $request->input('selected', true);
        $cart = session()->get('cart', []);

        foreach ($cart as $productId => $item) {
            $cart[$productId]['selected'] = $selected;
        }

        session()->put('cart', $cart);
        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật trạng thái chọn tất cả thành công.'
        ]);
    }

    /**
     * Phương thức pay(): Chuẩn bị dữ liệu và hiển thị trang thanh toán đơn hàng (Checkout).
     * Hàm này thực hiện:
     *   - Lấy giỏ hàng từ session. Nếu trống, chuyển hướng về trang giỏ hàng kèm lỗi.
     *   - Lấy thông tin sản phẩm và giá bán thực tế (ưu tiên giá Flash Sale nếu có, hoặc giá gốc).
     *   - Kiểm tra và giữ chỗ số lượng sản phẩm Flash Sale trong DB (lockCartFlashSale) để tránh người khác mua mất trong lúc thanh toán.
     *   - Tính toán tổng tiền tạm tính, số tiền được giảm giá từ voucher, và số tiền giảm từ điểm tích lũy thành viên.
     *   - Truy vấn số dư điểm ví của người dùng hiện tại để hiển thị.
     */
    public function pay(Request $request, PointsService $pointsService)
    {
        // 1. Lấy dữ liệu giỏ hàng đang lưu trong Session
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống.');
        }

        // 2. Định dạng lại danh sách sản phẩm, lấy thông tin hình ảnh, tên và giá tiền thực tế
        $cartItems = collect($cart)->map(function ($item, $id) {
            $product = Product::find($id);
            if (!$product) {
                return null;
            }
            // Sử dụng giá flash sale nếu sản phẩm đang chạy flash sale, ngược lại dùng giá gốc
            $effectivePrice = isset($item['flash_sale_price']) ? (int) $item['flash_sale_price'] : (int) ($item['price'] ?? $product->base_price);
            return [
                'id' => $id,
                'name' => $product->name,
                'price' => $effectivePrice,
                'quantity' => $item['quantity'],
                'selected' => $item['selected'] ?? true,
                'image' => $product->thumbnail,
                'url' => route('product.show', $id)
            ];
        })->filter()->values();

        // 3. Lọc ra các sản phẩm được tích chọn mua
        $selectedItems = $cartItems->filter(fn($i) => $i['selected'])->values();
        if ($selectedItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.');
        }

        // Chuyển danh sách sản phẩm chọn mua thành dạng mảng
        $selectedCart = collect($cart)->filter(fn($item) => $item['selected'] ?? true)->toArray();

        // 4. Gọi Service thực hiện giữ chỗ (khóa) số lượng Flash Sale trong database
        $lockOk = $this->flashSaleService->lockCartFlashSale($selectedCart);
        if (! $lockOk) {
            return redirect()->route('cart.index')->with('error', 'Một số sản phẩm Flash Sale đã hết số lượng hoặc hết hạn. Vui lòng kiểm tra lại giỏ hàng.');
        }

        // Đánh dấu giỏ hàng đã được khóa thành công
        session()->put('cart_locked', true);

        // 5. Tính toán các giá trị tiền bạc
        $checkoutItems = $selectedItems;
        $subtotal = (int) $checkoutItems->sum(fn ($item) => $item['price'] * $item['quantity']);
        $discount = (int) session('checkout_discount', 0); // Tiền giảm giá từ voucher
        $walletPointsUsed = (int) session('checkout_wallet_points', 0); // Số điểm ví sử dụng
        $walletReduction = $walletPointsUsed * PointsService::POINT_VALUE; // Quy đổi điểm sang tiền VND
        $finalAmount = max(0, $subtotal - $discount - $walletReduction); // Tổng số tiền khách phải trả thực tế

        // Lấy số dư điểm và xếp hạng thẻ của người dùng
        $balance = Auth::check()
            ? $pointsService->getBalance(Auth::user())
            : ['wallet_points' => 0, 'rank_points' => 0, 'current_rank' => 'Bronze'];

        // Trả về giao diện thanh toán kèm các thông số tính toán được
        return view('frontend.cart.pay', [
            'cartItems' => $checkoutItems,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'walletPointsUsed' => $walletPointsUsed,
            'walletReduction' => $walletReduction,
            'finalAmount' => $finalAmount,
            'balance' => $balance
        ]);
    }

    /**
     * Phương thức applyWalletPoints(): Xử lý áp dụng điểm tích lũy của khách hàng để giảm tiền trực tiếp trên hóa đơn.
     * Trả về kết quả JSON thông qua AJAX.
     */
    public function applyWalletPoints(Request $request, PointsService $pointsService)
    {
        // Xác thực số điểm gửi lên từ client phải là số nguyên dương
        $data = $request->validate([
            'wallet_points' => ['required', 'integer', 'min:0'],
        ]);

        if (!Auth::check()) {
            return response()->json(['message' => 'Vui lòng đăng nhập để dùng điểm.'], 401);
        }

        // Lấy số dư điểm hiện có của khách hàng từ DB
        $balance = $pointsService->getBalance(Auth::user());
        // Giới hạn số điểm áp dụng không được vượt quá số điểm hiện có của ví khách
        $walletPoints = min((int) $data['wallet_points'], (int) $balance['wallet_points']);

        // Lưu trữ số điểm áp dụng vào Session để tính toán lúc checkout
        session(['checkout_wallet_points' => $walletPoints]);

        return response()->json([
            'success' => true,
            'wallet_points' => $walletPoints,
            'wallet_reduction' => $walletPoints * PointsService::POINT_VALUE, // Tiền được giảm (VND)
        ]);
    }

    /**
     * Phương thức validateVoucher(): Xác thực mã giảm giá (voucher) nhập vào qua AJAX.
     * Tính toán số tiền được giảm và lưu vào Session.
     */
    public function validateVoucher(Request $request)
    {
        // Xác thực mã code và tổng tiền hàng gửi lên
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'subtotal' => ['required', 'integer', 'min:1'],
        ]);

        // Kiểm tra mã giảm giá thuộc loại nào và tính số tiền được chiết khấu
        [$discount, $message] = $this->resolveVoucherDiscount((string) $data['code'], (int) $data['subtotal']);
        
        // Nếu mã không hợp lệ hoặc số tiền chiết khấu bằng 0
        if ($discount <= 0) {
            session(['checkout_discount' => 0]);
            return response()->json([
                'success' => false,
                'message' => $message ?: 'Mã không hợp lệ.',
            ], 422);
        }

        // Lưu số tiền được giảm từ voucher vào Session
        session(['checkout_discount' => $discount]);

        return response()->json([
            'success' => true,
            'discount' => $discount,
            'message' => $message ?: 'Áp dụng mã giảm giá thành công.',
        ]);
    }

    /**
     * Phương thức placeOrder(): Xử lý ghi nhận và tạo đơn hàng từ form checkout (dành cho API cũ).
     * Bọc toàn bộ quá trình cập nhật database trong một DB Transaction để đảm bảo tính an toàn dữ liệu.
     */
    public function placeOrder(Request $request, PointsService $pointsService)
    {
        // Xác thực các trường dữ liệu thông tin giao hàng cơ bản
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string'],
            'note' => ['nullable', 'string'],
            'payment_method' => ['required', 'in:COD,VNPAY,MoMo,Cash_POS,Installment'],
        ]);

        $cart = session()->get('cart', []);
        $cartItems = $this->mapCartItems($cart);
        if ($cartItems->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng trống.'], 422);
        }

        // Tính toán các giá trị tiền bạc
        $subtotal = (int) $cartItems->sum(fn ($item) => $item['price'] * $item['quantity']);
        $shippingFee = 0;
        $discount = (int) session('checkout_discount', 0);
        $walletPointsUsed = (int) session('checkout_wallet_points', 0);
        $walletReduction = $walletPointsUsed * PointsService::POINT_VALUE;
        $finalAmount = max(0, $subtotal + $shippingFee - $discount - $walletReduction);
        $user = Auth::user();

        try {
            // Chạy Transaction cập nhật dữ liệu hàng loạt
            $order = DB::transaction(function () use ($data, $cartItems, $subtotal, $shippingFee, $discount, $walletPointsUsed, $finalAmount, $user, $pointsService, $cart) {
                // 1. Tạo bản ghi đơn hàng mới
                $order = Order::create([
                    'user_id' => $user?->user_id,
                    'order_type' => 'Online',
                    'total_amount' => $subtotal,
                    'shipping_fee' => $shippingFee,
                    'discount_amount' => $discount,
                    'wallet_points_used' => $walletPointsUsed,
                    'final_amount' => $finalAmount,
                    'payment_method' => $data['payment_method'],
                    'shipping_partner' => null,
                    'tracking_code' => null,
                    'status' => 'Pending',
                    'customer_name' => $data['customer_name'],
                    'customer_phone' => $data['customer_phone'],
                    'shipping_address' => $data['shipping_address'],
                    'note' => $data['note'] ?? null,
                    'payment_status' => in_array($data['payment_method'], ['COD', 'Cash_POS'], true) ? 'pending' : 'paid',
                    'order_code' => 'ORD' . now()->format('YmdHis') . random_int(100, 999),
                ]);

                // 2. Tạo chi tiết các mặt hàng trong đơn hàng
                foreach ($cartItems as $item) {
                    OrderDetail::create([
                        'order_id' => $order->order_id,
                        'item_id' => $item['id'],
                        'product_name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'price' => $item['price'] * $item['quantity'],
                    ]);
                }

                // 3. Xác nhận và trừ tồn kho các sản phẩm Flash Sale đang giữ chỗ
                $this->flashSaleService->confirmCartFlashSale($cart);

                // 4. Nếu có sử dụng điểm ví, tiến hành trừ điểm trong database
                if ($walletPointsUsed > 0 && $user) {
                    $pointsService->deductWalletPoints($user, $walletPointsUsed, $order, 'Dùng điểm tiêu dùng khi đặt hàng');
                }

                // 5. Xóa sạch session giỏ hàng và các session giảm giá tạm tính
                session()->forget(['cart', 'checkout_wallet_points', 'checkout_discount', 'cart_locked']);

                return $order;
            });

            return response()->json([
                'success' => true,
                'redirect_url' => route('cart.confirmation', $order->order_id),
                'order_id' => $order->order_id,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Hiển thị trang xác nhận đặt hàng thành công.
     */
    public function confirmation(int $orderId)
    {
        $order = Order::with(['details'])->findOrFail($orderId);
        return view('frontend.cart.confirmation', compact('order'));
    }

    /**
     * Phương thức confirmOrder(): Xử lý đặt hàng chính thức từ trang thanh toán (AJAX).
     * Đây là hàm xử lý quan trọng chứa toàn bộ các kiểm tra bảo mật phía Server:
     *   - Tải sản phẩm từ Session (Tránh bypass F12 thay đổi giá).
     *   - Kiểm tra mã voucher (Bao gồm voucher tĩnh và mã đổi thưởng thuộc sở hữu của riêng User trong DB).
     *   - Chặn Replay Attack bằng cách đổi trạng thái voucher sang 'used' ngay lập tức.
     *   - Thực thi Laravel Validation cho Họ tên (chữ), Số điện thoại (9-10 chữ số), Địa chỉ nhận hàng.
     *   - Trừ tồn kho biến thể sản phẩm, cập nhật trạng thái kho sang 'Sold'.
     *   - Khấu trừ tồn kho Flash Sale thực tế.
     *   - Reset giỏ hàng.
     */
    public function confirmOrder(Request $request)
    {
        // 1. Đọc giỏ hàng hiện tại và lọc ra các sản phẩm được chọn mua
        $cart = session()->get('cart', []);
        $selectedCart = collect($cart)->filter(fn($item) => $item['selected'] ?? true)->toArray();

        if (empty($selectedCart)) {
            return response()->json(['status' => 'error', 'message' => 'Giỏ hàng trống hoặc chưa chọn sản phẩm.'], 400);
        }

        // Tính tổng tiền gốc từ giỏ hàng (Server-side calculation)
        $totalAmount = collect($selectedCart)->reduce(fn($sum, $item) => $sum + ($item['price'] * $item['quantity']), 0);

        // 2. Xử lý mã giảm giá / Voucher
        $couponCode = strtoupper((string) (session('applied_coupon_code') ?: $request->input('discount_code', '')));
        [$discount] = $this->resolveVoucherDiscount($couponCode, (int) $totalAmount);

        // Nếu mã không khớp với voucher hệ thống, kiểm tra xem có phải là Voucher đổi thưởng động của User không
        if ($discount <= 0 && $couponCode !== '') {
            $redemption = \App\Models\RewardRedemption::with('reward')
                ->where('redemption_code', $couponCode)
                ->where('user_id', auth()->id()) // RÀNG BUỘC: Bắt buộc mã phải thuộc sở hữu của chính User đang login
                ->first();

            // Nếu mã tồn tại, hợp lệ và chưa hết hạn sử dụng
            if ($redemption && in_array($redemption->status, ['issued', 'approved', 'won'], true) && (!$redemption->expires_at || !$redemption->expires_at->isPast())) {
                $reward = $redemption->reward;
                if ($reward) {
                    // Áp dụng số tiền được chiết khấu dựa theo loại voucher (tiền mặt / tiền ship)
                    if ($reward->reward_type === 'shipping') {
                        $discount = (int) $reward->shipping_discount_amount;
                    } elseif ($reward->reward_type === 'wheel_prize') {
                        $discount = $reward->discount_amount > 0
                            ? (int) $reward->discount_amount
                            : (int) $reward->shipping_discount_amount;
                    } else {
                        $discount = (int) $reward->discount_amount;
                    }

                    $discount = min($discount, $totalAmount);

                    // CẬP NHẬT TRẠNG THÁI VOUCHER THÀNH used ĐỂ CHỐNG SPAM SỬ DỤNG LẠI (Anti Replay Attack)
                    $redemption->status = 'used';
                    $redemption->used_at = now();
                    $redemption->save();
                }
            }
        }
        
        // Tính tổng tiền cuối cùng phải thanh toán
        $finalAmount = $totalAmount - $discount;

        // 3. XÁC THỰC THÔNG TIN GIAO HÀNG PHÍA SERVER-SIDE (Chống bypass F12 HTML)
        $request->validate([
            // Họ tên chỉ nhận chữ và khoảng trắng, độ dài từ 2 đến 50 ký tự
            'name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[^0-9!@#$%^&*()_+=\[\]{}|\\:;"\'<>,.?\/~`]+$/u'],
            // Số điện thoại Việt Nam bắt đầu bằng số 0, độ dài 9-10 chữ số
            'phone' => ['required', 'string', 'regex:/^0[0-9]{8,9}$/'],
            // Địa chỉ nhận hàng từ 10 đến 150 ký tự, không chứa ký tự đặc biệt nguy hiểm
            'address' => ['required', 'string', 'min:10', 'max:150', 'regex:/^[^!@#$%^&*()_+=\[\]{}|\\:;"\'<>?~`]+$/u'],
            // Ghi chú đơn hàng tối đa 250 ký tự
            'note' => ['nullable', 'string', 'max:250'],
        ]);

        $name = $request->input('name');
        $phone = $request->input('phone');
        $address = $request->input('address');
        $note = $request->input('note');
        // Quyết định phương thức thanh toán
        $paymentMethod = $request->input('payment_method') === 'qr' ? 'VNPAY' : 'COD';

        // 4. Ghi nhận thông tin đơn hàng mới vào DB
        $order = Order::create([
            'user_id' => auth()->id(),
            'order_type' => 'Online',
            'total_amount' => $totalAmount,
            'shipping_fee' => 0,
            'discount_amount' => $discount,
            'wallet_points_used' => 0,
            'final_amount' => $finalAmount > 0 ? $finalAmount : 0,
            'payment_method' => $paymentMethod,
            'status' => 'Pending',
            'customer_name' => $name,
            'customer_phone' => $phone,
            'shipping_address' => $address,
            'note' => $note,
            'order_code' => 'ORD' . now()->format('YmdHis') . random_int(100, 999),
        ]);

        // 5. Duyệt qua từng sản phẩm trong giỏ hàng để cập nhật kho và tạo Chi tiết đơn hàng
        foreach ($selectedCart as $productId => $item) {
            $qty = $item['quantity'];
            $price = $item['price'];

            // Lấy biến thể sản phẩm (variant). Nếu chưa có thì tự động tạo biến thể mặc định
            $variant = ProductVariant::where('product_id', $productId)->first();
            if (!$variant) {
                $variant = ProductVariant::create([
                    'product_id' => $productId,
                    'color' => 'Mặc định',
                    'stock' => 99,
                ]);
            }

            // Truy vấn các IMEI sản phẩm cụ thể đang có trạng thái Trong kho (In_Stock)
            $inventoryItems = InventoryItem::where('variant_id', $variant->variant_id)
                ->where('status', 'In_Stock')
                ->take($qty)
                ->get();

            // Nếu số lượng IMEI thực tế trong kho thiếu so với khách đặt, tự động sinh mã IMEI giả lập cho đơn hàng Online
            $needed = $qty - $inventoryItems->count();
            for ($i = 0; $i < $needed; $i++) {
                $newItem = InventoryItem::create([
                    'variant_id' => $variant->variant_id,
                    'po_id' => PurchaseOrder::first()?->po_id ?? 1,
                    'imei_serial' => 'ONLINE-' . strtoupper(Str::random(12)),
                    'warehouse_loc' => 'Kho Online',
                    'status' => 'In_Stock',
                ]);
                $inventoryItems->push($newItem);
            }

            // Ghi nhận chi tiết đơn hàng cho từng sản phẩm và đánh dấu trạng thái IMEI là Sold (Đã bán)
            foreach ($inventoryItems as $invItem) {
                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'item_id' => $invItem->item_id,
                    'price' => $price,
                ]);
                
                $invItem->status = 'Sold';
                $invItem->save();
            }
        }

        // 6. Xác nhận giảm số lượng tồn kho của Flash Sale thực tế
        $this->flashSaleService->confirmCartFlashSale($selectedCart);

        // 7. Loại bỏ các sản phẩm đã mua ra khỏi giỏ hàng Session, chỉ giữ lại các sản phẩm không chọn mua
        $remainingCart = collect($cart)->filter(fn($item) => !($item['selected'] ?? true))->toArray();
        if (empty($remainingCart)) {
            session()->forget('cart');
        } else {
            session()->put('cart', $remainingCart);
        }
        
        // Xóa các session giảm giá tạm tính sau khi đặt hàng thành công
        session()->forget(['cart_locked', 'checkout_discount', 'applied_coupon_code']);

        return response()->json([
            'status' => 'success',
            'order_id' => $order->order_id,
            'total_amount' => $finalAmount,
            'message' => 'Đặt hàng thành công!'
        ]);
    }

    /**
     * Phương thức cancelOrder(): Hủy đơn hàng đang chờ và giải phóng (hoàn lại) số lượng Flash Sale đã khóa.
     */
    public function cancelOrder(Request $request)
    {
        $cart = session()->get('cart', []);
        // Giải phóng số lượng Flash Sale đã khóa giữ chỗ về lại kho bán
        $this->flashSaleService->releaseCartFlashSale($cart);
        
        // Xóa giỏ hàng
        session()->forget(['cart', 'cart_locked']);
        return redirect()->route('cart.index')->with('success', 'Đã hủy đơn hàng và hoàn lại số lượng Flash Sale.');
    }

    /**
     * Phương thức timeoutOrder(): Giải phóng số lượng Flash Sale khi quá trình thanh toán bị hết thời gian chờ (timeout).
     */
    public function timeoutOrder(Request $request)
    {
        $cart = session()->get('cart', []);
        $this->flashSaleService->releaseCartFlashSale($cart);
        session()->forget(['cart', 'cart_locked']);
        return redirect()->route('cart.index')->with('error', 'Đơn hàng đã hết hạn thanh toán. Số lượng Flash Sale đã được hoàn lại.');
    }

    public function ai(Request $request)
    {
        $orderId = $request->query('order_id');
        $order = Order::with('details.inventoryItem.variant.product')->find($orderId);
        return view('frontend.cart.maQR', compact('order'));
    }

    public function tracking()
    {
        return view('frontend.cart.ordertracking');
    }

    public function print()
    {
        return view('frontend.cart.print');
    }

    protected function mapCartItems(array $cart)
    {
        return collect($cart)->map(function ($item, $id) {
            $product = Product::find($id);
            if (!$product) {
                return null;
            }

            return [
                'id' => (int) $id,
                'name' => $product->name,
                'price' => (int) ($item['price'] ?? $product->base_price),
                'quantity' => (int) $item['quantity'],
                'image' => $product->thumbnail,
            ];
        })->filter()->values();
    }

    /**
     * Lấy số lượng sản phẩm trong giỏ hàng (session).
     */
    public function getCartCount()
    {
        $cart = session()->get('cart', []);
        return response()->json(['cart_count' => $this->getCartTotalQuantity($cart)]);
    }

    private function getCartTotalQuantity($cart)
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += (int)($item['quantity'] ?? 0);
        }
        return $total;
    }

    public function applyCoupon(Request $request)
    {
        $code = strtoupper($request->input('code'));
        if (!$code) {
            session()->forget(['checkout_discount', 'applied_coupon_code']);
            return response()->json(['success' => true, 'discount' => 0, 'message' => 'Đã xóa mã giảm giá.']);
        }

        $cart = session()->get('cart', []);
        $selectedCart = collect($cart)->filter(fn($item) => $item['selected'] ?? true)->toArray();
        if (empty($selectedCart)) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng trống hoặc chưa chọn sản phẩm.']);
        }

        $totalAmount = collect($selectedCart)->reduce(fn($sum, $item) => $sum + ($item['price'] * $item['quantity']), 0);

        // 1. Kiểm tra voucher hệ thống/coupon flash sale
        [$discount, $message] = $this->resolveVoucherDiscount($code, (int) $totalAmount);
        if ($discount > 0) {
            session()->put('checkout_discount', $discount);
            session()->put('applied_coupon_code', $code);
            return response()->json([
                'success' => true,
                'discount' => $discount,
                'message' => $message ?: 'Áp dụng mã giảm giá thành công!'
            ]);
        }

        // 2. Kiểm tra mã đổi thưởng trong DB
        $redemption = \App\Models\RewardRedemption::with('reward')
            ->where('redemption_code', $code)
            ->where('user_id', auth()->id())
            ->first();

        if (!$redemption) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá không tồn tại hoặc không hợp lệ.']);
        }

        if (!in_array($redemption->status, ['issued', 'approved', 'won'])) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá này đã được sử dụng hoặc không khả dụng.']);
        }

        if ($redemption->expires_at && $redemption->expires_at->isPast()) {
            return response()->json(['success' => false, 'message' => 'Mã giảm giá này đã hết hạn sử dụng.']);
        }

        $reward = $redemption->reward;
        if (!$reward) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông tin phần thưởng tương ứng.']);
        }

        // Tính toán giảm giá từ phần thưởng
        $discount = 0;
        if ($reward->reward_type === 'voucher') {
            $discount = (int) $reward->discount_amount;
        } elseif ($reward->reward_type === 'shipping') {
            $discount = (int) $reward->shipping_discount_amount;
        } elseif ($reward->reward_type === 'wheel_prize') {
            $discount = $reward->discount_amount > 0 
                ? (int) $reward->discount_amount 
                : (int) $reward->shipping_discount_amount;
        }

        if ($discount <= 0) {
            return response()->json(['success' => false, 'message' => 'Mã này không có giá trị giảm giá cho đơn hàng.']);
        }

        // Khống chế không giảm quá giá trị đơn hàng
        if ($discount > $totalAmount) {
            $discount = $totalAmount;
        }

        session()->put('checkout_discount', $discount);
        session()->put('applied_coupon_code', $code);

        return response()->json([
            'success' => true,
            'discount' => $discount,
            'message' => 'Áp dụng mã đổi thưởng "' . $reward->name . '" thành công! Giảm ' . number_format($discount) . 'đ.'
        ]);
    }

    public function discountCodeView()
    {
        $myVouchers = [];
        if (auth()->check()) {
            $myVouchers = \App\Models\RewardRedemption::with('reward')
                ->where('user_id', auth()->id())
                ->whereIn('status', ['issued', 'approved', 'won'])
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->get();
        }
        
        $balance = [];
        if (auth()->check()) {
            $balance = \App\Models\UserPoint::where('user_id', auth()->id())->first();
        }

        return view('frontend.cart.Applydiscountcode', compact('myVouchers', 'balance'));
    }

    private function resolveVoucherDiscount(string $rawCode, int $subtotal): array
    {
        $code = strtoupper(trim($rawCode));
        if ($code === '' || $subtotal <= 0) {
            return [0, 'Mã giảm giá không hợp lệ.'];
        }

        if ($code === 'PRO10') {
            return [(int) round($subtotal * 0.1), 'Áp dụng mã giảm giá 10% thành công!'];
        }

        $voucher = CouponFlashSale::query()
            ->where('promo_type', 'Coupon')
            ->where('code', $code)
            ->first();

        if (!$voucher) {
            return [0, 'Mã không tồn tại.'];
        }

        $now = now();
        if ($voucher->start_time && $now->lt(\Carbon\Carbon::parse($voucher->start_time))) {
            return [0, 'Mã chưa đến thời gian sử dụng.'];
        }
        if ($voucher->end_time && $now->gt(\Carbon\Carbon::parse($voucher->end_time))) {
            return [0, 'Mã đã hết hạn.'];
        }

        $discountType = $voucher->discount_type ?? 'fixed';
        $discountVal = (float) $voucher->discount_val;
        $discount = $discountType === 'percent'
            ? (int) round($subtotal * ($discountVal / 100))
            : (int) round($discountVal);

        $discount = max(0, min($discount, $subtotal));
        if ($discount <= 0) {
            return [0, 'Mã không tạo ra giảm giá hợp lệ.'];
        }

        return [$discount, 'Áp dụng mã thành công.'];
    }

    public function searchOrder(Request $request)
    {
        $code = $request->query('code');
        if (!$code) {
            return response()->json(['success' => false, 'message' => 'Vui lòng nhập mã đơn hàng.'], 400);
        }

        // Tìm đơn hàng theo order_code hoặc order_id
        $order = Order::with(['details.inventoryItem.variant.product'])
            ->where('order_code', $code)
            ->orWhere('order_id', $code)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Mã đơn hàng không tồn tại.'], 404);
        }

        // Áp dụng map trạng thái
        $statusMap = [
            'Pending'   => ['label' => 'CHỜ XỬ LÝ', 'color' => 'bg-yellow-100 text-yellow-700'],
            'BaoCK'     => ['label' => 'BÁO CK CHỜ DUYỆT', 'color' => 'bg-blue-100 text-blue-700'],
            'Shipping'  => ['label' => 'ĐANG GIAO HÀNG', 'color' => 'bg-emerald-100 text-emerald-700'],
            'Delivered' => ['label' => 'HOÀN THÀNH', 'color' => 'bg-green-100 text-green-700'],
            'Cancelled' => ['label' => 'ĐÃ HỦY', 'color' => 'bg-red-100 text-red-700'],
        ];

        $st = $statusMap[$order->status] ?? ['label' => strtoupper($order->status), 'color' => 'bg-slate-100 text-slate-600'];

        $items = $order->details->map(function ($detail) {
            $variant = $detail->inventoryItem->variant ?? null;
            $product = $variant->product ?? null;

            // Resolve ảnh sản phẩm thành URL đầy đủ (nhất quán với product_grid)
            $image = null;
            if ($product) {
                $thumb = $product->thumbnail;
                if ($thumb && \Illuminate\Support\Str::startsWith($thumb, 'http')) {
                    // thumbnail đã là URL đầy đủ
                    $image = $thumb;
                } else {
                    // Thử lấy từ mảng images
                    $rawImages = $product->images;
                    if ($rawImages) {
                        $arr = is_string($rawImages) ? json_decode($rawImages, true) : $rawImages;
                        $first = is_array($arr) && count($arr) > 0 ? $arr[0] : null;
                        if ($first && \Illuminate\Support\Str::startsWith($first, 'http')) {
                            $image = $first;
                        } elseif ($first) {
                            $image = asset('storage/' . ltrim($first, '/'));
                        }
                    }
                    // Fallback về uploads/products/ như product_grid làm
                    if (!$image) {
                        $image = $thumb
                            ? asset('uploads/products/' . $thumb)
                            : null;
                    }
                }
            }

            $productName = $detail->product_name
                ?? ($product->name ?? 'Sản phẩm không xác định');

            if ($variant && $variant->label) {
                $productName .= ' - ' . $variant->label;
            }

            return [
                'product_name' => $productName,
                'image' => $image,
                'quantity' => 1,
                'price' => (int) $detail->price,
            ];
        });

        // Gom nhóm sản phẩm cùng tên + giá lại thành 1 dòng cộng dồn số lượng
        $groupedItems = $items->groupBy(function ($item) {
            return $item['product_name'] . '_' . $item['price'];
        })->map(function ($group) {
            $first = $group->first();
            return [
                'product_name' => $first['product_name'],
                'image' => $first['image'],
                'quantity' => $group->count(),
                'price' => $first['price'],
                'subtotal' => $first['price'] * $group->count(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'order_id' => $order->order_id,
            'order_code' => $order->order_code,
            'customer_name' => $order->customer_name ?? ($order->user->full_name ?? 'N/A'),
            'customer_phone' => $order->customer_phone ?? ($order->user->phone_number ?? 'N/A'),
            'shipping_address' => $order->shipping_address ?? ($order->user->address ?? 'N/A'),
            'note' => $order->note,
            'payment_method' => $order->payment_method,
            'status' => $order->status,
            'status_label' => $st['label'],
            'status_color' => $st['color'],
            'total_amount' => (int) $order->total_amount,
            'shipping_fee' => (int) $order->shipping_fee,
            'discount_amount' => (int) ($order->discount_amount ?? 0),
            'final_amount' => (int) $order->final_amount,
            'items' => $groupedItems,
        ]);
    }
}
