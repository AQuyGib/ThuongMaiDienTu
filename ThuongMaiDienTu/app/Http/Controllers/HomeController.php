<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Lấy danh sách sản phẩm mới nhất, tối đa 8 sản phẩm
        $products = \App\Models\Product::orderBy('product_id', 'desc')->take(8)->get();
        return view('home', compact('products'));
    }
}
