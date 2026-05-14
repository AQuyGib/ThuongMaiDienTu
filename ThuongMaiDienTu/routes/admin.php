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
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\FlashSaleProductController;
use App\Http\Controllers\CashbookController;
use App\Http\Controllers\Admin\ThemeSettingController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\RoleController;

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

// Cấu hình giao diện
Route::get('/settings/theme', [ThemeSettingController::class, 'index'])->name('settings.theme');
Route::post('/settings/theme', [ThemeSettingController::class, 'update'])->name('settings.theme.update');
Route::post('/settings/theme/reset', [ThemeSettingController::class, 'reset'])->name('settings.theme.reset');

// ===== Quản lý Đơn hàng =====
// Route::resource('orders', OrderController::class);
// Route::post('orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

// CRUD Quyền hạn & Tài khoản (Permissions)
Route::resource('permissions', UserController::class)->names([
    'index' => 'users.index',
    'store' => 'users.store',
    'update' => 'users.update',
    'destroy' => 'users.destroy',
])->except(['create', 'show', 'edit']);

Route::get('permissions/{id}/sessions', [UserController::class, 'showSessions'])->name('users.sessions');
Route::delete('permissions/sessions/{sessionId}', [UserController::class, 'deleteSession'])->name('users.sessions.destroy');
Route::post('permissions/{id}/revoke-sessions', [UserController::class, 'revokeSessions'])->name('users.revoke');

// Quản lý Vai trò (Roles)
Route::resource('roles', RoleController::class)->names([
    'store' => 'roles.store',
    'update' => 'roles.update',
    'destroy' => 'roles.destroy',
])->only(['store', 'update', 'destroy']);

// Quản lý Giỏ hàng & Phí vận chuyển
Route::get('/shoppingcart', [CartController::class, 'index'])->name('cart.index');
Route::get('/ShippingCosts', [CartController::class, 'shipping'])->name('cart.ShippingCosts');
Route::get('/pay', [CartController::class, 'pay'])->name('cart.pay');
Route::get('/ai', [CartController::class, 'ai'])->name('cart.qr');

// ===== Quản lý Bài viết (Articles / Ecosystem) =====
Route::resource('articles', ArticleController::class);
Route::post('articles/{id}/approve', [ArticleController::class, 'approve'])->name('articles.approve');

// ===== Quản lý Nhà Cung Cấp =====
Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
Route::put('/suppliers/{id}', [SupplierController::class, 'update'])->name('suppliers.update');
Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

// ===== Quản lý Sản Phẩm & Danh Mục =====
Route::resource('products', ProductController::class);
Route::resource('categories', CategoryController::class);

// ===== Flash Sale =====
Route::resource('flash-sales', FlashSaleController::class)->except(['create', 'edit', 'show']);
Route::post('flash-sales/{flash_sale}/products', [FlashSaleProductController::class, 'store'])->name('flash-sales.products.store');
Route::delete('flash-sales/{flash_sale}/products/{flash_sale_product}', [FlashSaleProductController::class, 'destroy'])->name('flash-sales.products.destroy');

// ===== Quản lý Biến Thể & Bán Kèm =====
Route::post('/products/{id}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
Route::put('/products/{id}/variants/{variantId}', [ProductController::class, 'updateVariant'])->name('products.variants.update');
Route::delete('/products/{id}/variants/{variantId}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');
Route::post('/products/{id}/cross-sells', [ProductController::class, 'syncCrossSells'])->name('products.cross-sells.sync');

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
