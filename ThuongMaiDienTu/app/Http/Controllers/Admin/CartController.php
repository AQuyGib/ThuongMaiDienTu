<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Hiển thị giỏ hàng.
     */
    public function index()
    {
        return view('frontend.cart.shoppingcart');
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
}
