<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationCampaignController;
use App\Http\Controllers\CashbookController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\ProductFilterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RewardsController;
use App\Http\Controllers\RewardsHistoryController;
use App\Http\Controllers\Admin\RewardsController as AdminRewardsController;
use App\Http\Controllers\Admin\RewardImageController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\Admin\CommentManagementController;

// Authentication
Route::get('/login-register', [AuthController::class, 'index'])->name('login_register');
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset
Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendOtp'])->name('password.email');
Route::get('/verify-otp', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showVerifyOtpForm'])->name('password.verify.form');
Route::post('/verify-otp', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'verifyOtp'])->name('password.verify.post');
Route::get('/reset-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetPasswordForm'])->name('password.reset.form');
Route::post('/reset-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'resetPassword'])->name('password.reset.post');

// Social Login
Route::get('/auth/{provider}', [SocialController::class, 'redirectToProvider'])->name('social.login');
Route::get('/auth/{provider}/callback', [SocialController::class, 'handleProviderCallback']);

// Two-Factor Authentication (2FA)
Route::get('/2fa/verify',  [TwoFactorController::class, 'show'])->name('2fa.show');
Route::post('/2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify');
Route::post('/2fa/send',   [TwoFactorController::class, 'send'])->name('2fa.send');
Route::post('/2fa/toggle', [TwoFactorController::class, 'toggle'])->name('2fa.toggle')->middleware('auth');
Route::post('/2fa/toggle-request', [TwoFactorController::class, 'toggleRequest'])->name('2fa.toggle.request')->middleware('auth');
Route::post('/2fa/toggle-confirm', [TwoFactorController::class, 'toggleConfirm'])->name('2fa.toggle.confirm')->middleware('auth');
Route::get('/security',    [TwoFactorController::class, 'securityPage'])->name('security')->middleware('auth');
Route::delete('/security/session/{id}', [TwoFactorController::class, 'logoutSession'])->name('security.session.destroy')->middleware('auth');

// Language Switcher
Route::get('/locale/{locale}', function (string $locale) {
    $supported = array_keys(config('translatable.supported_locales', ['vi' => 'Tiếng Việt', 'en' => 'English']));
    if (in_array($locale, $supported)) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('locale.switch');

// Frontend
Route::get('/', function () {
    return redirect()->route('home');
});
Route::get('/Home', [HomeController::class, 'index'])->name('home');
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy')->middleware('auth');
Route::post('/reviews/{id}/report', [ReviewController::class, 'report'])->name('reviews.report')->middleware('auth');

// Modules
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/shoppingcart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/toggle-select', [CartController::class, 'toggleSelect'])->name('cart.toggleSelect');
Route::post('/cart/toggle-all', [CartController::class, 'toggleAll'])->name('cart.toggleAll');
Route::get('/ShippingCosts', [CartController::class, 'shipping'])->name('cart.shipping');
Route::get('/pay', [CartController::class, 'pay'])->middleware('auth')->name('cart.pay');
Route::post('/pay/wallet-points', [CartController::class, 'applyWalletPoints'])->name('cart.pay.wallet-points');
Route::post('/cart/validate-voucher', [CartController::class, 'validateVoucher'])->name('cart.voucher.validate');
Route::post('/pay/place-order', [CartController::class, 'placeOrder'])->name('cart.place-order');
Route::get('/order-confirmation/{orderId}', [CartController::class, 'confirmation'])->name('cart.confirmation');
Route::post('/cart/confirm', [CartController::class, 'confirmOrder'])->name('cart.confirm');
Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.apply-coupon');
Route::get('/pay/discount-code', [CartController::class, 'discountCodeView'])->name('cart.discount-code');
Route::post('/cart/cancel', [CartController::class, 'cancelOrder'])->name('cart.cancel');
Route::post('/cart/timeout', [CartController::class, 'timeoutOrder'])->name('cart.timeout');
Route::get('/maQR', [CartController::class, 'ai'])->name('cart.qr');
Route::get('/orders', [CartController::class, 'tracking'])->name('cart.tracking');
Route::get('/orders/search', [CartController::class, 'searchOrder'])->name('cart.orders.search');
Route::get('/print-bill', [CartController::class, 'print'])->name('cart.print');
Route::get('/cart/count', [CartController::class, 'getCartCount'])->name('cart.count');

Route::get('/rewards', [RewardsController::class, 'index'])->name('rewards.index');
Route::get('/rewards/history', [RewardsHistoryController::class, 'index'])->name('rewards.history');
Route::get('/rewards/{reward}', [RewardsController::class, 'show'])->name('rewards.show');
Route::post('/rewards/redeem', [RewardsController::class, 'redeem'])->name('rewards.redeem');
Route::post('/rewards/spin', [RewardsController::class, 'spin'])->name('rewards.spin');

Route::middleware('auth')->group(function () {
    Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/{video}/stream', [VideoController::class, 'stream'])->name('videos.stream');
    Route::post('/videos/{video}/like', [VideoController::class, 'like'])->name('videos.like');
    Route::post('/videos/{video}/view', [VideoController::class, 'view'])->name('videos.view');
    Route::get('/videos/{video}/comments', [VideoController::class, 'getComments'])->name('videos.comments.index');
    Route::post('/videos/{video}/comments', [VideoController::class, 'storeComment'])->name('videos.comments.store');
    Route::delete('/videos/comments/{comment}', [VideoController::class, 'destroyComment'])->name('videos.comments.destroy');
    Route::post('/videos/comments/{comment}/report', [VideoController::class, 'reportComment'])->name('videos.comments.report');
});

// Articles & Lifestyle
Route::get('/lifestyle', [\App\Http\Controllers\ArticleFrontendController::class, 'index'])->name('articles.index');
Route::middleware('auth')->group(function() {
    Route::get('/lifestyle/create', [\App\Http\Controllers\ArticleFrontendController::class, 'create'])->name('articles.create');
    Route::post('/lifestyle/store', [\App\Http\Controllers\ArticleFrontendController::class, 'store'])->name('articles.store');
    Route::get('/lifestyle/{id}/edit', [\App\Http\Controllers\ArticleFrontendController::class, 'edit'])->name('articles.edit');
    Route::put('/lifestyle/{id}', [\App\Http\Controllers\ArticleFrontendController::class, 'update'])->name('articles.update');
    Route::delete('/lifestyle/{id}', [\App\Http\Controllers\ArticleFrontendController::class, 'destroy'])->name('articles.destroy');
    Route::post('/lifestyle/ai-assist', [\App\Http\Controllers\ArticleFrontendController::class, 'aiAssist'])->name('articles.ai-assist');
});
Route::get('/lifestyle/{slug}', [\App\Http\Controllers\ArticleFrontendController::class, 'show'])->name('articles.show');

Route::middleware(['auth', \App\Http\Middleware\IsAdmin::class])->prefix('admin')->group(function () {
    Route::get('/notifications/dashboard', [NotificationCampaignController::class, 'dashboard'])->name('admin.notifications.dashboard');
    Route::get('/notifications', [NotificationCampaignController::class, 'index'])->name('admin.notifications.index');
    Route::get('/notifications/create', [NotificationCampaignController::class, 'create'])->name('admin.notifications.create');
    Route::get('/notifications/unread-count', [NotificationCampaignController::class, 'unreadCount'])->name('admin.notifications.unread-count');
    Route::get('/notifications/search-promo', [NotificationCampaignController::class, 'searchPromo'])->name('admin.notifications.search-promo');
    Route::get('/notifications/search-users', [NotificationCampaignController::class, 'searchUsers'])->name('admin.notifications.search-users');
    Route::post('/notifications', [NotificationCampaignController::class, 'store'])->name('admin.notifications.store');
    Route::get('/notifications/{notification}', [NotificationCampaignController::class, 'show'])->name('admin.notifications.show');
    Route::patch('/notifications/{notification}/read', [NotificationCampaignController::class, 'markAsRead'])->name('admin.notifications.read');
    Route::delete('/notifications/{notification}', [NotificationCampaignController::class, 'destroy'])->name('admin.notifications.destroy');
    Route::post('/notifications/bulk-delete', [NotificationCampaignController::class, 'bulkDestroy'])->name('admin.notifications.bulk-destroy');
    Route::post('/notifications/low-stock-check', [NotificationCampaignController::class, 'lowStockCheck'])->name('admin.notifications.low-stock-check');
});

// ============================================================
// ROUTE: SẢN PHẨM FRONTEND (PRODUCT LISTING & DETAIL)
// Các route phục vụ trang danh sách sản phẩm, lọc nâng cao theo danh mục,
// và trang chi tiết sản phẩm (product.show) cho khách hàng.
// Controller: Frontend\ProductController (index, show)
// ============================================================

// Trang danh sách tất cả sản phẩm (có hỗ trợ phân trang, lọc theo query params)
Route::get('/products', [App\Http\Controllers\Frontend\ProductController::class, 'index'])->name('products.index');
// API endpoint lọc sản phẩm AJAX (được gọi từ JavaScript bộ lọc nâng cao)
Route::get('/products/filter', [ProductFilterController::class, 'filterProducts'])->name('products.filter');
// Trang danh sách sản phẩm theo danh mục cụ thể (VD: /products/dien-thoai)
Route::get('/products/{categorySlug}', [App\Http\Controllers\Frontend\ProductController::class, 'index'])->name('products.category');
// Trang chi tiết sản phẩm - hiển thị đầy đủ thông tin, biến thể, đánh giá, combo, cross-sell
Route::get('/san-pham/{id}', [App\Http\Controllers\Frontend\ProductController::class, 'show'])->name('product.show');
// Redirect từ URL cũ /product/{id} sang URL mới /san-pham/{id} để giữ tương thích ngược (backward compatible)
Route::get('/product/{id}', function ($id) {
    return redirect()->route('product.show', $id);
})->name('product.legacy');
// API endpoint lấy danh sách bộ lọc đặc thù theo danh mục (RAM, ROM, Chip...) cho sidebar lọc
Route::get('/api/categories/{id}/filters', [ProductFilterController::class, 'getCategoryFilters'])->name('api.categories.filters');

// Admin Customer Management
Route::prefix('admin')->middleware(['auth', \App\Http\Middleware\IsAdmin::class])->group(function () {
    Route::get('customers/trash', [\App\Http\Controllers\Admin\CustomerController::class, 'trash'])->name('admin.customers.trash');
    Route::post('customers/{id}/restore', [\App\Http\Controllers\Admin\CustomerController::class, 'restore'])->name('admin.customers.restore');
    Route::delete('customers/{id}/force-delete', [\App\Http\Controllers\Admin\CustomerController::class, 'forceDelete'])->name('admin.customers.force-delete');
    Route::get('customers/export', [\App\Http\Controllers\Admin\CustomerController::class, 'export'])->name('admin.customers.export');
    Route::post('customers/bulk-action', [\App\Http\Controllers\Admin\CustomerController::class, 'bulkAction'])->name('admin.customers.bulk-action');
    Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class, ['as' => 'admin']);
    Route::get('products/import', [\App\Http\Controllers\Admin\ProductController::class, 'importForm'])->name('admin.products.import.form');
    Route::post('products/import', [\App\Http\Controllers\Admin\ProductController::class, 'importExcel'])->name('admin.products.import');
    Route::get('products/export', [\App\Http\Controllers\Admin\ProductController::class, 'exportExcel'])->name('admin.products.export');
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class, ['as' => 'admin']);
    Route::post('products/{product}/variants', [\App\Http\Controllers\Admin\ProductController::class, 'storeVariant'])->name('admin.products.variants.store');
    Route::put('products/{product}/variants/{variant}', [\App\Http\Controllers\Admin\ProductController::class, 'updateVariant'])->name('admin.products.variants.update');
    Route::delete('products/{product}/variants/{variant}', [\App\Http\Controllers\Admin\ProductController::class, 'destroyVariant'])->name('admin.products.variants.destroy');

    Route::get('rewards/history', [AdminRewardsController::class, 'history'])->name('admin.rewards.history');
    Route::get('rewards', [AdminRewardsController::class, 'index'])->name('admin.rewards.index');
    Route::post('rewards', [AdminRewardsController::class, 'store'])->name('admin.rewards.store');
    Route::post('rewards/setting', [AdminRewardsController::class, 'updateSetting'])->name('admin.rewards.update-setting');
    Route::post('rewards/wheels', [AdminRewardsController::class, 'updateLuckyWheels'])->name('admin.rewards.update-lucky-wheels');
    Route::put('rewards/{reward}', [AdminRewardsController::class, 'update'])->name('admin.rewards.update');
    Route::put('rewards/{reward}/image', [RewardImageController::class, 'update'])->name('admin.rewards.image.update');
    Route::delete('rewards/{reward}', [AdminRewardsController::class, 'destroy'])->name('admin.rewards.destroy');

    // Comment & Review Management
    Route::get('comments', [CommentManagementController::class, 'index'])->name('admin.comments.index');
    Route::post('comments/reviews/bulk-delete', [CommentManagementController::class, 'bulkDeleteReviews'])->name('admin.comments.reviews.bulk-delete');
    Route::post('comments/video-comments/bulk-delete', [CommentManagementController::class, 'bulkDeleteVideoComments'])->name('admin.comments.video-comments.bulk-delete');
    Route::delete('comments/reviews/{id}', [CommentManagementController::class, 'destroyReview'])->name('admin.comments.reviews.destroy');
    Route::delete('comments/video-comments/{id}', [CommentManagementController::class, 'destroyVideoComment'])->name('admin.comments.video-comments.destroy');
    Route::post('comments/reviews/{id}/reply', [CommentManagementController::class, 'replyReview'])->name('admin.comments.reviews.reply');
    Route::post('comments/video-comments/{id}/reply', [CommentManagementController::class, 'replyVideoComment'])->name('admin.comments.video-comments.reply');
    Route::post('comments/reviews/{id}/approve', [CommentManagementController::class, 'approveReview'])->name('admin.comments.reviews.approve');
    Route::post('comments/video-comments/{id}/approve', [CommentManagementController::class, 'approveVideoComment'])->name('admin.comments.video-comments.approve');
    Route::post('comments/reviews/{id}/clear-reports', [CommentManagementController::class, 'clearReviewReports'])->name('admin.comments.reviews.clear-reports');
    Route::post('comments/video-comments/{id}/clear-reports', [CommentManagementController::class, 'clearVideoCommentReports'])->name('admin.comments.video-comments.clear-reports');
    Route::post('comments/users/{id}/unban', [CommentManagementController::class, 'unbanUser'])->name('admin.comments.users.unban');

    // Installment Management
    Route::get('installments', [\App\Http\Controllers\Admin\InstallmentController::class, 'index'])->name('admin.installments.index');
    Route::get('installments/create', [\App\Http\Controllers\Admin\InstallmentController::class, 'create'])->name('admin.installments.create');
    Route::post('installments', [\App\Http\Controllers\Admin\InstallmentController::class, 'store'])->name('admin.installments.store');
    Route::get('installments/{id}', [\App\Http\Controllers\Admin\InstallmentController::class, 'show'])->name('admin.installments.show');
    Route::get('installments/{id}/edit', [\App\Http\Controllers\Admin\InstallmentController::class, 'edit'])->name('admin.installments.edit');
    Route::put('installments/{id}', [\App\Http\Controllers\Admin\InstallmentController::class, 'update'])->name('admin.installments.update');
    Route::delete('installments/{id}', [\App\Http\Controllers\Admin\InstallmentController::class, 'destroy'])->name('admin.installments.destroy');
    Route::post('installments/{id}/approve', [\App\Http\Controllers\Admin\InstallmentController::class, 'approve'])->name('admin.installments.approve');
    Route::post('installments/{id}/reject', [\App\Http\Controllers\Admin\InstallmentController::class, 'reject'])->name('admin.installments.reject');
    Route::get('installments/{id}/invoice', [\App\Http\Controllers\Admin\InstallmentController::class, 'printInvoice'])->name('admin.installments.invoice');
    Route::post('installments/payments/{id}/pay', [\App\Http\Controllers\Admin\InstallmentController::class, 'payMonth'])->name('admin.installments.pay-month');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/repair-tickets/ai-diagnose', [ProfileController::class, 'aiDiagnoseRepairTicket'])->name('profile.repair-tickets.ai-diagnose');
    Route::post('/profile/repair-tickets', [ProfileController::class, 'storeRepairTicket'])->name('profile.repair-tickets.store');
    Route::post('/profile/address', [ProfileController::class, 'addAddress'])->name('profile.address.store');
    Route::post('/profile/address/{id}', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::delete('/profile/address/{id}', [ProfileController::class, 'deleteAddress'])->name('profile.address.destroy');
    Route::delete('/wishlist/clear', [WishlistController::class, 'clearWishlist'])->name('wishlist.clear');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'removeFromWishlist'])->name('wishlist.destroy');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

// Search & Suggestions
use App\Http\Controllers\Frontend\SearchController;
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/api/search/suggestions', [SearchController::class, 'suggestions'])->name('api.search.suggestions');
Route::get('/api/category-products/{id}', [SearchController::class, 'getProductsByCategory'])->name('api.category.products');

// Policy & Warranty Routes
use App\Http\Controllers\Frontend\WarrantyController;
use App\Http\Controllers\PolicyController;

Route::get('/chinh-sach-bao-hanh', [PolicyController::class, 'warranty'])->name('policy.warranty');
Route::get('/chinh-sach-doi-tra', [PolicyController::class, 'returnPolicy'])->name('policy.return');
Route::get('/warranty', [WarrantyController::class, 'index'])->name('warranty.index');
Route::post('/warranty/lookup', [WarrantyController::class, 'lookup'])->name('warranty.lookup');
Route::get('/return-policy', [WarrantyController::class, 'returnPolicy'])->name('warranty.return');

// Product Compare
Route::get('/compare', [CompareController::class, 'index'])->name('compare.index');
Route::get('/compare/data', [CompareController::class, 'data'])->name('compare.data');
Route::post('/compare/sync', [CompareController::class, 'sync'])->name('compare.sync');
Route::get('/api/products/search-compare', [CompareController::class, 'searchCompare'])->name('api.products.search-compare');

// AI Chatbot
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\InstallmentController;

Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot.chat');
Route::post('/chatbot/create-ticket', [ChatbotController::class, 'createTicketFromChat'])->name('chatbot.create-ticket');
Route::post('/installments/register', [InstallmentController::class, 'register'])->name('installments.register')->middleware('auth');
