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
        $stats = [
            'total_products' => \App\Models\Product::count(),
            'total_users'    => \App\Models\User::count(),
            'total_orders'   => \App\Models\Order::count(),
            'total_income'   => \App\Models\Cashbook::ofType('Income')->sum('amount'),
            'total_expense'  => \App\Models\Cashbook::ofType('Expense')->sum('amount'),
            'recent_orders'  => \App\Models\Order::with('user')->orderBy('order_id', 'desc')->take(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
