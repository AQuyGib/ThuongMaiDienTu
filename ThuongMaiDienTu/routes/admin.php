<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\CashbookController;

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
Route::get('/kpi', [App\Http\Controllers\Admin\KPIController::class, 'index'])->name('kpi.index');

// CRUD Tài khoản (Users)
Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
Route::get('users/{id}/sessions', [UserController::class, 'showSessions'])->name('users.sessions');
Route::delete('users/sessions/{sessionId}', [UserController::class, 'deleteSession'])->name('users.sessions.destroy');
Route::post('users/{id}/revoke-sessions', [UserController::class, 'revokeSessions'])->name('users.revoke');

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

// Quản lý Giỏ hàng & Phí vận chuyển
Route::get('/shoppingcart', [CartController::class, 'index'])->name('cart.index');
Route::get('/ShippingCosts', [CartController::class, 'shipping'])->name('cart.shipping');
Route::get('/pay', [CartController::class, 'pay'])->name('cart.pay');
Route::get('/ai', [CartController::class, 'ai'])->name('cart.ai');

// ===== Quản lý Bài viết (Articles / Ecosystem) =====
Route::resource('articles', ArticleController::class);
Route::post('articles/{id}/approve', [ArticleController::class, 'approve'])->name('articles.approve');

// ===== Quản lý Nhà Cung Cấp =====
Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

// ===== Quản lý Biến Thể Sản Phẩm =====
Route::post('/products/{id}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
Route::put('/products/{id}/variants/{variantId}', [ProductController::class, 'updateVariant'])->name('products.variants.update');
Route::delete('/products/{id}/variants/{variantId}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');

// ===== Phiếu Nhập Kho =====
Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
Route::get('/purchase-orders/{id}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');

// ===== Quản lý IMEI =====
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::put('/inventory/{id}/status', [InventoryController::class, 'updateStatus'])->name('inventory.updateStatus');

// ===== Quản lý Sổ Quỹ (Cashbook) =====
Route::post('cashbooks/bulk-destroy', [CashbookController::class, 'bulkDestroy'])->name('cashbooks.bulkDestroy');
Route::resource('cashbooks', CashbookController::class);

// API lấy variants theo product (cho form tạo phiếu nhập)
Route::get('/api/products/{id}/variants', [PurchaseOrderController::class, 'getVariants'])->name('api.product.variants');
