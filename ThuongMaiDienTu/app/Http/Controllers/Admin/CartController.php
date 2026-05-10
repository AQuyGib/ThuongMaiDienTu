<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;

class CartController extends Controller
{
    /**
     * Hiển thị giỏ hàng.
     */
    public function index()
    {
        // Lấy 3 sản phẩm đầu tiên từ database để giả lập giỏ hàng
        $products = Product::limit(3)->get();
        
        $cartItems = $products->map(function($product) {
            return [
                'id' => $product->product_id,
                'name' => $product->name,
                'price' => (int)$product->base_price,
                'quantity' => rand(1, 2),
                'stock' => 10,
                'selected' => true,
                'image' => $product->thumbnail,
                'url' => '#'
            ];
        });

        return view('frontend.cart.shoppingcart', compact('cartItems'));
    }

    /**
     * Hiển thị trang tính phí vận chuyển.
     */
    public function shipping()
    {
        return view('frontend.cart.ShippingCosts');
    }

    /**
     * Thêm sản phẩm vào giỏ hàng (session-based).
     */
    public function addToCart(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity  = max(1, (int) $request->input('quantity', 1));

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Sản phẩm không tồn tại.'], 404);
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'id'       => $product->product_id,
                'name'     => $product->name,
                'price'    => (int) $product->base_price,
                'quantity' => $quantity,
                'stock'    => 99,
                'selected' => true,
                'image'    => $product->thumbnail,
                'url'      => route('product.detail', $product->product_id),
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => "Đã thêm \"{$product->name}\" vào giỏ hàng!",
            'cart_count' => count($cart),
        ]);
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
