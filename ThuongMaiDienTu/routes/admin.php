<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\InventoryMovementController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\FlashSaleProductController;
use App\Http\Controllers\CashbookController;
use App\Http\Controllers\Admin\ThemeSettingController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RepairTicketInvoiceController;
use App\Http\Controllers\Admin\ServiceInvoiceController;
use App\Http\Controllers\Admin\VideoManagementController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/kpi', [App\Http\Controllers\Admin\KPIController::class, 'index'])->name('kpi.index');

// Cấu hình giao diện
Route::get('/settings/theme', [ThemeSettingController::class, 'index'])->name('settings.theme');
Route::post('/settings/theme', [ThemeSettingController::class, 'update'])->name('settings.theme.update');
Route::post('/settings/theme/reset', [ThemeSettingController::class, 'reset'])->name('settings.theme.reset');

// ===== Video Management =====
Route::get('/videos', [VideoManagementController::class, 'index'])->name('videos.index');
Route::get('/videos/create', [VideoManagementController::class, 'create'])->name('videos.create');
Route::post('/videos', [VideoManagementController::class, 'store'])->name('videos.store');
Route::get('/videos/{video}', [VideoManagementController::class, 'show'])->name('videos.show');
Route::get('/videos/{video}/edit', [VideoManagementController::class, 'edit'])->name('videos.edit');
Route::put('/videos/{video}', [VideoManagementController::class, 'update'])->name('videos.update');
Route::patch('/videos/{video}/approve', [VideoManagementController::class, 'approve'])->name('videos.approve');
Route::patch('/videos/{video}/hide', [VideoManagementController::class, 'hide'])->name('videos.hide');
Route::delete('/videos/{video}', [VideoManagementController::class, 'destroy'])->name('videos.destroy');
Route::delete('/videos/comments/{comment}', [VideoManagementController::class, 'destroyComment'])->name('videos.comments.destroy');


// ===== Quản lý Đơn hàng =====
Route::resource('orders', OrderController::class);
Route::post('orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

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
Route::get('/products/{product}/translation/en', [\App\Http\Controllers\Admin\ProductTranslationController::class, 'edit'])->name('products.translation.edit');
Route::put('/products/{product}/translation/en', [\App\Http\Controllers\Admin\ProductTranslationController::class, 'update'])->name('products.translation.update');
Route::get('/products/export', [ProductController::class, 'exportExcel'])->name('products.export');
Route::get('/products/template', [ProductController::class, 'downloadTemplate'])->name('products.template');
Route::get('/products/import', [ProductController::class, 'importForm'])->name('products.import.form');
Route::post('/products/import', [ProductController::class, 'importExcel'])->name('products.import');
Route::resource('categories', CategoryController::class);
Route::get('/categories/{category}/translation/en', [\App\Http\Controllers\Admin\CategoryTranslationController::class, 'edit'])->name('categories.translation.edit');
Route::put('/categories/{category}/translation/en', [\App\Http\Controllers\Admin\CategoryTranslationController::class, 'update'])->name('categories.translation.update');
Route::resource('attributes', AttributeController::class);
Route::get('/attributes/{attribute}/translation/en', [\App\Http\Controllers\Admin\AttributeTranslationController::class, 'edit'])->name('attributes.translation.edit');
Route::put('/attributes/{attribute}/translation/en', [\App\Http\Controllers\Admin\AttributeTranslationController::class, 'update'])->name('attributes.translation.update');
Route::resource('pages', PageController::class);
Route::get('/pages/{page}/translation/en', [\App\Http\Controllers\Admin\PageTranslationController::class, 'edit'])->name('pages.translation.edit');
Route::put('/pages/{page}/translation/en', [\App\Http\Controllers\Admin\PageTranslationController::class, 'update'])->name('pages.translation.update');

// ===== Flash Sale =====
Route::resource('flash-sales', FlashSaleController::class)->except(['create', 'edit', 'show']);
Route::post('flash-sales/{flash_sale}/products', [FlashSaleProductController::class, 'store'])->name('flash-sales.products.store');
Route::delete('flash-sales/{flash_sale}/products/{flash_sale_product}', [FlashSaleProductController::class, 'destroy'])->name('flash-sales.products.destroy');

// ===== Quản lý Biến Thể & Bán Kèm =====
Route::post('/products/{id}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
Route::put('/products/{id}/variants/{variantId}', [ProductController::class, 'updateVariant'])->name('products.variants.update');
Route::delete('/products/{id}/variants/{variantId}', [ProductController::class, 'destroyVariant'])->name('products.variants.destroy');
Route::post('/products/{id}/cross-sells', [ProductController::class, 'syncCrossSells'])->name('products.cross-sells.sync');
Route::post('/products/{id}/combos', [ProductController::class, 'syncCombos'])->name('products.combos.sync');

// ===== Phiếu Nhập Kho =====
Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
Route::get('/purchase-orders/{id}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');

// ===== Quản lý IMEI & Cảnh báo tồn kho =====
Route::get('/inventory/warnings', [InventoryController::class, 'warningList'])->name('inventory.warnings');
Route::get('/inventory/movements', [InventoryMovementController::class, 'index'])->name('inventory.movements');
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::put('/inventory/{id}/status', [InventoryController::class, 'updateStatus'])->name('inventory.updateStatus');

// ===== API & Điều chuyển nội bộ =====
Route::get('/api/inventory-by-warehouse', [\App\Http\Controllers\Admin\WarehouseTransferController::class, 'getInventoryByWarehouse'])->name('api.inventory-by-warehouse');
Route::post('/warehouse-transfers/{id}/complete', [\App\Http\Controllers\Admin\WarehouseTransferController::class, 'complete'])->name('warehouse-transfers.complete');
Route::post('/warehouse-transfers/{id}/cancel', [\App\Http\Controllers\Admin\WarehouseTransferController::class, 'cancel'])->name('warehouse-transfers.cancel');
Route::resource('warehouse-transfers', \App\Http\Controllers\Admin\WarehouseTransferController::class);

// ===== Quản lý Sổ Quỹ (Cashbook) =====
Route::post('cashbooks/bulk-destroy', [CashbookController::class, 'bulkDestroy'])->name('cashbooks.bulkDestroy');
Route::resource('cashbooks', CashbookController::class);

// API lấy variants theo product (cho form tạo phiếu nhập)
Route::get('/api/products/{id}/variants', [PurchaseOrderController::class, 'getVariants'])->name('api.product.variants');

// ===== Quản lý Khung Sản phẩm Trang chủ =====
use App\Http\Controllers\Admin\HomeSectionController;
Route::resource('home-sections', HomeSectionController::class);
Route::get('/api/products/search', [HomeSectionController::class, 'searchProducts'])->name('api.products.search');
Route::post('/home-sections/reorder', [HomeSectionController::class, 'reorder'])->name('home-sections.reorder');
Route::get('/api/customers/search-by-phone', [RepairTicketInvoiceController::class, 'searchByPhone'])->name('api.customers.search-by-phone');

// ===== Quản lý Hóa đơn Dịch vụ & Phiếu sửa chữa =====
Route::resource('service-invoices', ServiceInvoiceController::class);
Route::get('service-invoices/{serviceInvoice}/print', [ServiceInvoiceController::class, 'print'])->name('service-invoices.print');
Route::get('service-invoices/{serviceInvoice}/pdf', [ServiceInvoiceController::class, 'pdf'])->name('service-invoices.pdf');
Route::get('service-invoices/{serviceInvoice}/pdf/open', [ServiceInvoiceController::class, 'openPdf'])->name('service-invoices.pdf.open');
Route::get('service-invoices/{serviceInvoice}/pdf/save', [ServiceInvoiceController::class, 'savePdf'])->name('service-invoices.pdf.save');
Route::get('service-invoices/{serviceInvoice}/pdf/download', [ServiceInvoiceController::class, 'downloadSavedPdf'])->name('service-invoices.pdf.download');

Route::get('repair-tickets', [RepairTicketInvoiceController::class, 'index'])->name('repair-tickets.index');
Route::get('repair-tickets/create', [RepairTicketInvoiceController::class, 'createTicket'])->name('repair-tickets.create');
Route::post('repair-tickets', [RepairTicketInvoiceController::class, 'storeTicket'])->name('repair-tickets.store');
Route::get('repair-tickets/{repairTicket}/edit', [RepairTicketInvoiceController::class, 'editTicket'])->name('repair-tickets.edit');
Route::put('repair-tickets/{repairTicket}', [RepairTicketInvoiceController::class, 'updateTicket'])->name('repair-tickets.update');
Route::delete('repair-tickets/{repairTicket}', [RepairTicketInvoiceController::class, 'destroyTicket'])->name('repair-tickets.destroy');
Route::get('repair-tickets/{repairTicket}/invoice/create', [RepairTicketInvoiceController::class, 'create'])->name('repair-tickets.invoice.create');
Route::post('repair-tickets/invoice', [RepairTicketInvoiceController::class, 'store'])->name('repair-tickets.invoice.store');
