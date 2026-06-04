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
                $basePriceToDiscount = $salePrice ?? (int) $product->base_price;
                $appliedDiscount = false;

                // 1. Kiểm tra từ Cache Combo AI đã đề xuất
                $user = auth()->user();
                $userKey = $user ? $user->user_id : (session()->getId() ?? 'guest');
                $cacheKey = "ai_combo_recs_{$userKey}_{$parentProductId}";
                $cachedCombos = cache()->get($cacheKey);

                if (is_array($cachedCombos)) {
                    foreach ($cachedCombos as $combo) {
                        if ((int) $combo['product_id'] === $productId) {
                            $discountType = $combo['discount_type'];
                            $discountValue = (float) $combo['discount_value'];

                            if ($discountType === 'percentage') {
                                $salePrice = (int) ($basePriceToDiscount * (1 - $discountValue / 100));
                            } else {
                                $salePrice = (int) ($basePriceToDiscount - $discountValue);
                            }
                            $appliedDiscount = true;
                            break;
                        }
                    }
                }

                // 2. Fallback về cơ sở dữ liệu nếu không tìm thấy trong Cache AI
                if (!$appliedDiscount) {
                    $comboRelation = $parentProduct->comboProducts()->where('product_combos.combo_product_id', $productId)->first();
                    if ($comboRelation) {
                        $pivot = $comboRelation->pivot;
                        if ($pivot->discount_type === 'percentage') {
                            $salePrice = (int) ($basePriceToDiscount * (1 - $pivot->discount_value / 100));
                        } else {
                            $salePrice = (int) ($basePriceToDiscount - $pivot->discount_value);
                        }
                    }
                }

                if ($salePrice !== null && $salePrice < 0) {
                    $salePrice = 0;
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

        $addresses = auth()->check() ? auth()->user()->addresses()->orderByDesc('is_default')->get() : collect();

        return view('frontend.cart.ShippingCosts', compact('cartItems', 'addresses'));
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
            $appliedCode = strtoupper(trim((string) session('applied_coupon_code', '')));

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

                session()->forget(['cart', 'checkout_wallet_points', 'checkout_discount', 'cart_locked', 'applied_coupon_code']);

                return $order;
            });

            // Tăng times_used cho voucher đã dùng (sau khi transaction commit thành công)
            if ($appliedCode !== '') {
                CouponFlashSale::where('promo_type', 'Coupon')
                    ->where('code', $appliedCode)
                    ->whereNotNull('usage_limit')
                    ->increment('times_used');
            }

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

    private function calculateServerShippingFee(string $province, int $totalAmount): int
    {
        // Ngưỡng miễn phí vận chuyển đồng nhất: 10.000.000đ
        $threshold = 10000000;

        // Nhóm 1: Nội thành (< 30 km) — 20.000đ
        $group1 = ['hcm', 'hn'];

        // Nhóm 2: Vùng lân cận (30–150 km) — 35.000đ
        $group2 = ['bd', 'dnai', 'la', 'tg', 'vt', 'bn', 'hy', 'hnam', 'vp'];

        // Nhóm 3: Vùng trung bình (150–400 km) — 50.000đ
        $group3 = ['hp', 'ct', 'hb', 'nb', 'ag', 'kg', 'dt', 'tv', 'bte'];

        // Nhóm 4: Vùng xa (400–700 km) — 70.000đ
        $group4 = ['dn', 'qng', 'bdinh', 'nth', 'th', 'qbi', 'hue'];

        // Nhóm 5: Vùng rất xa (> 700 km) — 100.000đ
        $group5 = ['gl', 'dkl', 'lc', 'dbi', 'ss', 'cb', 'ls', 'cm', 'other'];

        if (in_array($province, $group1)) {
            $fee = 20000;
        } elseif (in_array($province, $group2)) {
            $fee = 35000;
        } elseif (in_array($province, $group3)) {
            $fee = 50000;
        } elseif (in_array($province, $group4)) {
            $fee = 70000;
        } else {
            $fee = 100000; // group5 + fallback
        }

        return $totalAmount >= $threshold ? 0 : $fee;
    }

    public function confirmOrder(Request $request)
    {
        try {
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

                        $redemption->status = 'used';
                        $redemption->used_at = now();
                        $redemption->save();
                    }
                }
            }

            $request->validate([
                'name'     => ['required', 'string', 'min:2', 'max:50', 'regex:/^[^0-9!@#$%^&*()_+=\[\]{}|\\\\:;"\'<>,.?\/~`]+$/u'],
                'phone'    => ['required', 'string', 'regex:/^0[0-9]{8,9}$/'],
                'province' => ['nullable', 'string', 'in:hcm,hn,bd,dnai,la,tg,vt,bn,hy,hnam,vp,hp,ct,hb,nb,ag,kg,dt,tv,bte,dn,qng,bdinh,nth,th,qbi,hue,gl,dkl,lc,dbi,ss,cb,ls,cm,other'],
                'address'  => ['required', 'string', 'min:10', 'max:150', 'regex:/^[^!@#$%^&*()_+=\[\]{}|\\\\:;"\'<>?~`]+$/u'],
                'note'     => ['nullable', 'string', 'max:250'],
            ]);

            $name          = $request->input('name');
            $phone         = $request->input('phone');
            $province      = $request->input('province') ?? 'other';
            $address       = $request->input('address');
            $note          = $request->input('note');
            $paymentMethod = $request->input('payment_method') === 'qr' ? 'VNPAY' : 'COD';

            $shippingFee = $this->calculateServerShippingFee($province, (int) ($totalAmount - $discount));
            $finalAmount = $totalAmount - $discount + $shippingFee;

            $order = Order::create([
                'user_id'          => auth()->id(),
                'order_type'       => 'Online',
                'total_amount'     => $totalAmount,
                'shipping_fee'     => $shippingFee,
                'discount_amount'  => $discount,
                'wallet_points_used' => 0,
                'final_amount'     => $finalAmount > 0 ? $finalAmount : 0,
                'payment_method'   => $paymentMethod,
                'status'           => 'Pending',
                'customer_name'    => $name,
                'customer_phone'   => $phone,
                'shipping_address' => $address,
                'note'             => $note,
                'order_code'       => 'ORD' . now()->format('YmdHis') . random_int(100, 999),
            ]);

            foreach ($selectedCart as $productId => $item) {
                $qty   = $item['quantity'];
                $price = $item['price'];

                $variant = ProductVariant::where('product_id', $productId)->first();
                if (!$variant) {
                    $variant = ProductVariant::create([
                        'product_id' => $productId,
                        'color'      => 'Mặc định',
                        'stock'      => 99,
                    ]);
                }

                $inventoryItems = InventoryItem::where('variant_id', $variant->variant_id)
                    ->where('status', 'In_Stock')
                    ->take($qty)
                    ->get();

                $needed = $qty - $inventoryItems->count();
                for ($i = 0; $i < $needed; $i++) {
                    $newItem = InventoryItem::create([
                        'variant_id'   => $variant->variant_id,
                        'po_id'        => PurchaseOrder::first()?->po_id ?? 1,
                        'imei_serial'  => 'ONLINE-' . strtoupper(Str::random(12)),
                        'warehouse_loc'=> 'Kho Online',
                        'status'       => 'In_Stock',
                    ]);
                    $inventoryItems->push($newItem);
                }

                foreach ($inventoryItems as $invItem) {
                    OrderDetail::create([
                        'order_id' => $order->order_id,
                        'item_id'  => $invItem->item_id,
                        'price'    => $price,
                    ]);
                    $invItem->status = 'Sold';
                    $invItem->save();
                }
            }

            $this->flashSaleService->confirmCartFlashSale($selectedCart);

            // Tăng times_used cho voucher đã dùng
            $appliedCode = strtoupper(trim((string) session('applied_coupon_code', '')));
            if ($appliedCode !== '') {
                CouponFlashSale::where('promo_type', 'Coupon')
                    ->where('code', $appliedCode)
                    ->whereNotNull('usage_limit')
                    ->increment('times_used');
            }

            $remainingCart = collect($cart)->filter(fn($item) => !($item['selected'] ?? true))->toArray();
            if (empty($remainingCart)) {
                session()->forget('cart');
            } else {
                session()->put('cart', $remainingCart);
            }
            session()->forget(['cart_locked', 'checkout_discount', 'applied_coupon_code']);

            return response()->json([
                'status'       => 'success',
                'order_id'     => $order->order_id,
                'total_amount' => $finalAmount,
                'message'      => 'Đặt hàng thành công!'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error('confirmOrder error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }
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

    public function tracking(Request $request)
    {
        $orders = collect();

        if (Auth::check()) {
            $statusFilter = $request->query('status');
            $query = Order::with(['details.inventoryItem.variant.product'])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc');

            if ($statusFilter && $statusFilter !== 'all') {
                $query->where('status', $statusFilter);
            }

            $orders = $query->get()->map(function ($order) {
                // Gom nhóm sản phẩm từ details
                $items = $order->details->map(function ($detail) {
                    $variant = $detail->inventoryItem->variant ?? null;
                    $product = $variant?->product ?? null;

                    $image = null;
                    if ($product) {
                        $thumb = $product->thumbnail;
                        if ($thumb && \Illuminate\Support\Str::startsWith($thumb, 'http')) {
                            $image = $thumb;
                        } else {
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
                            if (!$image && $thumb) {
                                $image = asset('uploads/products/' . $thumb);
                            }
                        }
                    }

                    return [
                        'product_name' => $detail->product_name ?? ($product?->name ?? 'Sản phẩm không xác định'),
                        'image'        => $image,
                        'price'        => (int) $detail->price,
                    ];
                });

                // Gom nhóm theo tên + giá
                $groupedItems = $items->groupBy(fn($i) => $i['product_name'] . '_' . $i['price'])
                    ->map(fn($g) => [
                        'product_name' => $g->first()['product_name'],
                        'image'        => $g->first()['image'],
                        'quantity'     => $g->count(),
                        'price'        => $g->first()['price'],
                    ])->values();

                return [
                    'order_id'        => $order->order_id,
                    'order_code'      => $order->order_code,
                    'status'          => $order->status,
                    'payment_method'  => $order->payment_method,
                    'customer_name'   => $order->customer_name,
                    'customer_phone'  => $order->customer_phone,
                    'shipping_address'=> $order->shipping_address,
                    'final_amount'    => (int) $order->final_amount,
                    'created_at'      => $order->created_at,
                    'items'           => $groupedItems,
                ];
            });
        }

        return view('frontend.cart.ordertracking', compact('orders'));
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
        $code = strtoupper(trim((string) $request->input('code', '')));
        if (!$code) {
            session()->forget(['checkout_discount', 'applied_coupon_code']);
            return response()->json(['success' => true, 'discount' => 0, 'message' => 'Đã xóa mã giảm giá.']);
        }

        // Tính subtotal từ cart session (hoặc lấy từ request nếu truyền lên)
        $cart = session()->get('cart', []);
        $selectedCart = collect($cart)->filter(fn($item) => $item['selected'] ?? true)->toArray();

        if (!empty($selectedCart)) {
            $totalAmount = (int) collect($selectedCart)->reduce(fn($sum, $item) => $sum + ($item['price'] * $item['quantity']), 0);
        } else {
            // Fallback: lấy subtotal từ request (pay page gửi lên)
            $totalAmount = (int) $request->input('subtotal', 0);
        }

        if ($totalAmount <= 0) {
            return response()->json(['success' => false, 'message' => 'Không tính được giá trị đơn hàng. Vui lòng thử lại.']);
        }

        // 1. Kiểm tra voucher hệ thống/coupon flash sale
        [$discount, $message] = $this->resolveVoucherDiscount($code, $totalAmount);
        if ($discount > 0) {
            session()->put('checkout_discount', $discount);
            session()->put('applied_coupon_code', $code);
            return response()->json([
                'success' => true,
                'discount' => $discount,
                'message' => $message ?: 'Áp dụng mã giảm giá thành công!'
            ]);
        }

        // Nếu voucher tồn tại nhưng không hợp lệ (hết hạn, chưa đến ngày...), trả về đúng lỗi
        $voucherExists = CouponFlashSale::where('promo_type', 'Coupon')->where('code', $code)->exists();
        if ($voucherExists) {
            return response()->json(['success' => false, 'message' => $message ?: 'Mã không hợp lệ.'], 422);
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

        // Kiểm tra giới hạn lượt sử dụng
        if ($voucher->usage_limit !== null && $voucher->times_used >= $voucher->usage_limit) {
            return [0, 'Mã voucher đã hết lượt sử dụng.'];
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
