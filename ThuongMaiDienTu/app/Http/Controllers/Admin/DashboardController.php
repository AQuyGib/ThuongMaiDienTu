<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Trang Dashboard chính của Admin.
     * Hiện tại redirect sang trang Users, sau này sẽ build Dashboard riêng.
     */
    public function index()
    {
        return redirect()->route('admin.users.index');
    }
}
