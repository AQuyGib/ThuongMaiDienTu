<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Chứa toàn bộ các route liên quan đến trang quản trị (CMS/ERP).
| Các route này thường có tiền tố /admin và yêu cầu quyền admin.
|
*/

Route::get('/', function () {
    return "Trang Dashboard Admin";
})->name('dashboard');

// ===== Quản lý Danh Mục =====
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

// ===== Quản lý Sản Phẩm =====
Route::get('/sanpham', [ProductController::class, 'index'])->name('sanpham.index');
Route::post('/sanpham', [ProductController::class, 'store'])->name('sanpham.store');
Route::put('/sanpham/{id}', [ProductController::class, 'update'])->name('sanpham.update');
Route::delete('/sanpham/{id}', [ProductController::class, 'destroy'])->name('sanpham.destroy');
