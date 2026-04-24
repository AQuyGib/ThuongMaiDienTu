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
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
