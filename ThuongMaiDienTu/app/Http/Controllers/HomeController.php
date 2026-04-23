<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Hiện tại trả về view tĩnh để demo UI
        // Khi kết nối Database, có thể query Product::all() truyền vào đây.
        return view('home');
    }
}
