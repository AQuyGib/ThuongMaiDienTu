<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use Illuminate\Support\Str;
use App\Services\FlashSaleService;



class CartController extends Controller
{
    public function __construct(private readonly FlashSaleService $flashSaleService)
    {
    }

    /**
     * Hiển thị giỏ hàng.
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        // Chuyển đổi dữ liệu từ session sang format view yêu cầu
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
                'url' => route('product.show', $id)
            ];
        })->filter()->values();

        return view('frontend.cart.shoppingcart', compact('cartItems'));
    }

    /**
     * Thêm sản phẩm vào giỏ hàng.
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

        return response()->json(['status' => 'success', 'cart_count' => count($cart)]);
    }

    /**
     * Hiển thị trang tính phí vận chuyển.
     */
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
                'cart_count' => count($cart),
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
                'cart_count' => count($cart),
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

    public function pay(Request $request)
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
            $effectivePrice = isset($item['flash_sale_price']) ? (int) $item['flash_sale_price'] : (int) $product->base_price;
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
        return view('frontend.cart.pay', ['cartItems' => $selectedItems]);
    }

    public function confirmOrder(Request $request)
    {
        $cart = session()->get('cart', []);
        $selectedCart = collect($cart)->filter(fn($item) => $item['selected'] ?? true)->toArray();

        if (empty($selectedCart)) {
            return response()->json(['status' => 'error', 'message' => 'Giỏ hàng trống hoặc chưa chọn sản phẩm.'], 400);
        }

        $totalAmount = collect($selectedCart)->reduce(fn($sum, $item) => $sum + ($item['price'] * $item['quantity']), 0);
        
        $discount = 0;
        if ($request->input('discount_code') === 'PRO10') {
            $discount = (int) round($totalAmount * 0.1);
        }
        $finalAmount = $totalAmount - $discount;

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
            'final_amount' => $finalAmount > 0 ? $finalAmount : 0,
            'payment_method' => $paymentMethod,
            'status' => 'Pending',
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
                
                $invItem->status = 'Sold';
                $invItem->save();
            }
        }

        $this->flashSaleService->confirmCartFlashSale($selectedCart);

        $remainingCart = collect($cart)->filter(fn($item) => !($item['selected'] ?? true))->toArray();
        session()->put('cart', $remainingCart);
        session()->forget('cart_locked');

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

    /**
     * Lấy số lượng sản phẩm trong giỏ hàng (session).
     */
    public function getCartCount()
    {
        $cart = session()->get('cart', []);
        return response()->json(['cart_count' => count($cart)]);
    }
}
