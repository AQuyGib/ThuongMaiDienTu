<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    /**
     * Hiển thị trang tra cứu bảo hành (đã tách từ WarrantyController hoặc gộp chung)
     * Hoặc bạn muốn hiển thị chi tiết chính sách bảo hành.
     */
    public function warranty()
    {
        return view('policy.warranty');
    }

    /**
     * Hiển thị trang chính sách đổi trả
     */
    public function returnPolicy()
    {
        return view('policy.return_policy');
    }
}
