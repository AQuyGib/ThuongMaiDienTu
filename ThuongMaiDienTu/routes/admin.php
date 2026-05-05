<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;

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

// ===== Quản lý Danh Mục =====
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

// ===== Quản lý Sản Phẩm =====
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
