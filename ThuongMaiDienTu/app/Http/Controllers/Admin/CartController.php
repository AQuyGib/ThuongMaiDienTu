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

    public function pay(Request $request, PointsService $pointsService)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống.');
        }

        $cartItems = collect($cart)->map(function ($item, $id) {
            $product = Product::find($id);
            if (!$product) {
                return null;
            }
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

        $selectedItems = $cartItems->filter(fn($i) => $i['selected'])->values();
        if ($selectedItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.');
        }

        $selectedCart = collect($cart)->filter(fn($item) => $item['selected'] ?? true)->toArray();

        $lockOk = $this->flashSaleService->lockCartFlashSale($selectedCart);
        if (! $lockOk) {
            return redirect()->route('cart.index')->with('error', 'Một số sản phẩm Flash Sale đã hết số lượng hoặc hết hạn. Vui lòng kiểm tra lại giỏ hàng.');
        }

        session()->put('cart_locked', true);

        $checkoutItems = $selectedItems;
        $subtotal = (int) $checkoutItems->sum(fn ($item) => $item['price'] * $item['quantity']);
        $discount = (int) session('checkout_discount', 0);
        $walletPointsUsed = (int) session('checkout_wallet_points', 0);
        $walletReduction = $walletPointsUsed * PointsService::POINT_VALUE;
        $finalAmount = max(0, $subtotal - $discount - $walletReduction);
        $balance = Auth::check()
            ? $pointsService->getBalance(Auth::user())
            : ['wallet_points' => 0, 'rank_points' => 0, 'current_rank' => 'Bronze'];

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

    public function applyWalletPoints(Request $request, PointsService $pointsService)
    {
        $data = $request->validate([
            'wallet_points' => ['required', 'integer', 'min:0'],
        ]);

        if (!Auth::check()) {
            return response()->json(['message' => 'Vui lòng đăng nhập để dùng điểm.'], 401);
        }

        $balance = $pointsService->getBalance(Auth::user());
        $walletPoints = min((int) $data['wallet_points'], (int) $balance['wallet_points']);

        session(['checkout_wallet_points' => $walletPoints]);

        return response()->json([
            'success' => true,
            'wallet_points' => $walletPoints,
            'wallet_reduction' => $walletPoints * PointsService::POINT_VALUE,
        ]);
    }

    public function validateVoucher(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'subtotal' => ['required', 'integer', 'min:1'],
        ]);

        [$discount, $message] = $this->resolveVoucherDiscount((string) $data['code'], (int) $data['subtotal']);
        if ($discount <= 0) {
            session(['checkout_discount' => 0]);
            return response()->json([
                'success' => false,
                'message' => $message ?: 'Mã không hợp lệ.',
            ], 422);
        }

        session(['checkout_discount' => $discount]);

        return response()->json([
            'success' => true,
            'discount' => $discount,
            'message' => $message ?: 'Áp dụng mã giảm giá thành công.',
        ]);
    }

    public function placeOrder(Request $request, PointsService $pointsService)
    {
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

        $subtotal = (int) $cartItems->sum(fn ($item) => $item['price'] * $item['quantity']);
        $shippingFee = 0;
        $discount = (int) session('checkout_discount', 0);
        $walletPointsUsed = (int) session('checkout_wallet_points', 0);
        $walletReduction = $walletPointsUsed * PointsService::POINT_VALUE;
        $finalAmount = max(0, $subtotal + $shippingFee - $discount - $walletReduction);
        $user = Auth::user();

        try {
            $order = DB::transaction(function () use ($data, $cartItems, $subtotal, $shippingFee, $discount, $walletPointsUsed, $finalAmount, $user, $pointsService, $cart) {
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

                // Confirm flash sale
                $this->flashSaleService->confirmCartFlashSale($cart);

                if ($walletPointsUsed > 0 && $user) {
                    $pointsService->deductWalletPoints($user, $walletPointsUsed, $order, 'Dùng điểm tiêu dùng khi đặt hàng');
                }

                // Đồng bộ tồn kho và ghi nhận lịch sử biến động kho cho đơn hàng mới
                $order->load('details.inventoryItem.variant');
                app(\App\Services\InventoryService::class)->syncOrderByStatus($order, null, $order->status);

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

    public function confirmation(int $orderId)
    {
        $order = Order::with(['details'])->findOrFail($orderId);
        return view('frontend.cart.confirmation', compact('order'));
    }

    public function confirmOrder(Request $request)
    {
        $cart = session()->get('cart', []);
        $selectedCart = collect($cart)->filter(fn($item) => $item['selected'] ?? true)->toArray();

        if (empty($selectedCart)) {
            return response()->json(['status' => 'error', 'message' => 'Giỏ hàng trống hoặc chưa chọn sản phẩm.'], 400);
        }

        $totalAmount = collect($selectedCart)->reduce(fn($sum, $item) => $sum + ($item['price'] * $item['quantity']), 0);

        $couponCode = strtoupper((string) (session('applied_coupon_code') ?: $request->input('discount_code', '')));
        [$discount] = $this->resolveVoucherDiscount($couponCode, (int) $totalAmount);

        if ($discount <= 0 && $couponCode !== '') {
            $redemption = \App\Models\RewardRedemption::with('reward')
                ->where('redemption_code', $couponCode)
                ->where('user_id', auth()->id())
                ->first();

            if ($redemption && in_array($redemption->status, ['issued', 'approved', 'won'], true) && (!$redemption->expires_at || !$redemption->expires_at->isPast())) {
                $reward = $redemption->reward;
                if ($reward) {
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

                    // Đánh dấu mã đổi thưởng đã dùng sau khi đặt đơn thành công.
                    $redemption->status = 'used';
                    $redemption->used_at = now();
                    $redemption->save();
                }
            }
        }
        $finalAmount = $totalAmount - $discount;

        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[^0-9!@#$%^&*()_+=\[\]{}|\\:;"\'<>,.?\/~`]+$/u'],
            'phone' => ['required', 'string', 'regex:/^0[0-9]{8,9}$/'],
            'address' => ['required', 'string', 'min:10', 'max:150', 'regex:/^[^!@#$%^&*()_+=\[\]{}|\\:;"\'<>?~`]+$/u'],
            'note' => ['nullable', 'string', 'max:250'],
        ]);

        $name = $request->input('name');
        $phone = $request->input('phone');
        $address = $request->input('address');
        $note = $request->input('note');
        $paymentMethod = $request->input('payment_method') === 'qr' ? 'VNPAY' : 'COD';

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

        foreach ($selectedCart as $productId => $item) {
            $qty = $item['quantity'];
            $price = $item['price'];

            $variant = ProductVariant::where('product_id', $productId)->first();
            if (!$variant) {
                $variant = ProductVariant::create([
                    'product_id' => $productId,
                    'color' => 'Mặc định',
                    'stock' => 99,
                ]);
            }

            $inventoryItems = InventoryItem::where('variant_id', $variant->variant_id)
                ->where('status', 'In_Stock')
                ->take($qty)
                ->get();

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

            foreach ($inventoryItems as $invItem) {
                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'item_id' => $invItem->item_id,
                    'price' => $price,
                ]);
            }
        }

        // Đồng bộ tồn kho và ghi nhận lịch sử biến động kho cho đơn hàng mới
        $order->load('details.inventoryItem.variant');
        app(\App\Services\InventoryService::class)->syncOrderByStatus($order, null, $order->status);

        $this->flashSaleService->confirmCartFlashSale($selectedCart);

        $remainingCart = collect($cart)->filter(fn($item) => !($item['selected'] ?? true))->toArray();
        if (empty($remainingCart)) {
            session()->forget('cart');
        } else {
            session()->put('cart', $remainingCart);
        }
        session()->forget(['cart_locked', 'checkout_discount', 'applied_coupon_code']);

        return response()->json([
            'status' => 'success',
            'order_id' => $order->order_id,
            'total_amount' => $finalAmount,
            'message' => 'Đặt hàng thành công!'
        ]);
    }

    public function cancelOrder(Request $request)
    {
        $cart = session()->get('cart', []);
        $this->flashSaleService->releaseCartFlashSale($cart);
        session()->forget(['cart', 'cart_locked']);
        return redirect()->route('cart.index')->with('success', 'Đã hủy đơn hàng và hoàn lại số lượng Flash Sale.');
    }

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
}
