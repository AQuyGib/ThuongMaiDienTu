<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
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
                'selected' => true,
                'image' => $product->thumbnail,
                'url' => route('product.detail', $id)
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
        if ($flashSaleProduct && $this->flashSaleService->canApplySale($flashSaleProduct)) {
            $salePrice = (int) $flashSaleProduct->sale_price;
            $remaining = $this->flashSaleService->getRemainingQuantity($flashSaleProduct);
            if ($newQuantity > $remaining) {
                return back()->with('error', 'Số lượng Flash Sale còn lại không đủ.');
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

    /**
     * Hiển thị trang tính phí vận chuyển.
     */
    public function shipping()
    {
        return view('frontend.cart.ShippingCosts');
    }

    public function checkout()
    {
        return view('frontend.cart.pay');
    }

    public function pay(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return view('frontend.cart.pay')->with('error', 'Giỏ hàng trống.');
        }

        $lockOk = $this->flashSaleService->lockCartFlashSale($cart);
        if (! $lockOk) {
            return redirect()->route('cart.index')->with('error', 'Một số sản phẩm Flash Sale đã hết số lượng hoặc hết hạn. Vui lòng kiểm tra lại giỏ hàng.');
        }

        session()->put('cart_locked', true);
        return view('frontend.cart.pay', compact('cart'));
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
}
