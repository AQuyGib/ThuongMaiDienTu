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
}
