<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CartController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Chứa toàn bộ các route liên quan đến trang quản trị (CMS/ERP).
| Các route này có tiền tố /admin và sử dụng name prefix admin.
|
| Lưu ý: Hiện tại chưa thêm middleware auth vì hệ thống đăng nhập
| sẽ được xây dựng sau. Khi hoàn tất Auth, thêm ->middleware('auth')
|
*/

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// CRUD Tài khoản (Users)
Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
// Quản lý Giỏ hàng & Phí vận chuyển
Route::get('/shoppingcart', [CartController::class, 'index'])->name('cart.index');
Route::get('/ShippingCosts', [CartController::class, 'shipping'])->name('cart.shipping');
