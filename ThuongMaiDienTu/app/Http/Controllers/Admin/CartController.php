<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Hiển thị giỏ hàng.
     */
    public function index()
    {
        // Dữ liệu giỏ hàng mẫu (sau này sẽ lấy từ DB / Session)
        $cartItems = [
            [
                'id'       => 1,
                'name'     => 'Android Tivi Sony 4K 65 inch KD-65X80J',
                'image'    => 'https://images.unsplash.com/photo-1593359677879-a4bb92f4834c?w=300',
                'url'      => '#',
                'price'    => 16990000,
                'quantity' => 2,
                'stock'    => 10,
                'selected' => true,
            ],
            [
                'id'       => 2,
                'name'     => 'Tủ lạnh Aqua Inverter 189 lít AQR-T219FA(PB)',
                'image'    => 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=300',
                'url'      => '#',
                'price'    => 4990000,
                'quantity' => 1,
                'stock'    => 5,
                'selected' => true,
            ],
        ];

        return view('frontend.cart.shoppingcart', compact('cartItems'));
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

    public function pay()
    {
        return view('frontend.cart.pay');
    }

    public function ai()
    {
        return view('frontend.cart.maQR');
    }
}
