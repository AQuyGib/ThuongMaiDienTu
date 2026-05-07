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
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::post('/products', [ProductController::class, 'store'])->name('products.store');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

// ===== Quản lý Biến Thể Sản Phẩm =====
Route::post('/products/{id}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
Route::put('/products/{id}/variants/{variantId}', [ProductController::class, 'updateVariant'])->name('products.variants.update');
Route::delete('/products/{id}/variants/{variantId}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');
