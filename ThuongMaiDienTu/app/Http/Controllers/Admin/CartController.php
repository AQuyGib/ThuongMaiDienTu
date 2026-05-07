<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Hiển thị giỏ hàng (trong Admin).
     */
    public function index()
    {
        return view('frontend.cart.shoppingcart');
    }

    /**
     * Hiển thị trang tính phí vận chuyển (trong Admin).
     */
    public function shipping()
    {
        return view('frontend.cart.ShippingCosts');
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
