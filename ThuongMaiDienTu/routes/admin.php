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
Route::get('/danhmuc', [CategoryController::class, 'index'])->name('danhmuc.index');
Route::post('/danhmuc', [CategoryController::class, 'store'])->name('danhmuc.store');
Route::put('/danhmuc/{id}', [CategoryController::class, 'update'])->name('danhmuc.update');
Route::delete('/danhmuc/{id}', [CategoryController::class, 'destroy'])->name('danhmuc.destroy');

// ===== Quản lý Sản Phẩm =====
Route::get('/sanpham', [ProductController::class, 'index'])->name('sanpham.index');
Route::post('/sanpham', [ProductController::class, 'store'])->name('sanpham.store');
Route::put('/sanpham/{id}', [ProductController::class, 'update'])->name('sanpham.update');
Route::delete('/sanpham/{id}', [ProductController::class, 'destroy'])->name('sanpham.destroy');
