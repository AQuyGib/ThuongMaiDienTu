<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\InventoryItem;
use App\Models\OrderDetail;
use App\Models\Product;
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
                'selected' => true,
                'image' => $product->thumbnail,
                'url' => route('product.show', $id)
            ];
        })->filter()->values();

        return view('frontend.cart.shoppingcart', compact('cartItems'));
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
        } else {
            $cart[$productId] = [
                'name' => $product->name,
                'quantity' => $quantity,
                'price' => $salePrice ?? (int) $product->base_price,
                'flash_sale_price' => $salePrice,
                'image' => $product->thumbnail,
            ];
        }

        session()->put('cart', $cart);

        if ($request->has('buy_now')) {
            return redirect()->route('cart.index')->with('success', 'Đã thêm vào giỏ hàng!');
        }

        return response()->json(['status' => 'success', 'cart_count' => count($cart)]);
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
                'url' => route('product.detail', $id)
            ];
        })->filter()->values();

        return view('frontend.cart.ShippingCosts', compact('cartItems'));
    }

    public function checkout()
    {
        return view('frontend.cart.pay');
    }

    public function pay(Request $request, PointsService $pointsService)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống.');
        }

        $lockOk = $this->flashSaleService->lockCartFlashSale($cart);
        if (!$lockOk) {
            return redirect()->route('cart.index')->with('error', 'Một số sản phẩm Flash Sale đã hết số lượng hoặc hết hạn. Vui lòng kiểm tra lại giỏ hàng.');
        }

        session()->put('cart_locked', true);

        $cartItems = $this->mapCartItems($cart);
        $subtotal = (int) $cartItems->sum(fn ($item) => $item['price'] * $item['quantity']);
        $discount = (int) session('checkout_discount', 0);
        $walletPointsUsed = (int) session('checkout_wallet_points', 0);
        $walletReduction = $walletPointsUsed * PointsService::POINT_VALUE;
        $finalAmount = max(0, $subtotal - $discount - $walletReduction);
        $balance = Auth::check()
            ? $pointsService->getBalance(Auth::user())
            : ['wallet_points' => 0, 'rank_points' => 0, 'current_rank' => 'Bronze'];

        return view('frontend.cart.pay', compact(
            'cartItems',
            'subtotal',
            'discount',
            'walletPointsUsed',
            'walletReduction',
            'finalAmount',
            'balance'
        ));
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
        $this->flashSaleService->confirmCartFlashSale($cart);
        session()->forget(['cart', 'cart_locked']);
        return redirect()->route('home')->with('success', 'Đặt hàng thành công!');
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

    public function ai()
    {
        return view('frontend.cart.maQR');
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
        return response()->json(['cart_count' => count($cart)]);
    }
}
