<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\CashbookController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\ProductFilterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RewardsController;
use App\Http\Controllers\RewardsHistoryController;
use App\Http\Controllers\Admin\RewardsController as AdminRewardsController;
use App\Http\Controllers\Admin\RewardImageController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\CompareController;

// Authentication
Route::get('/login-register', [AuthController::class, 'index'])->name('login_register');
// Alias 'login' bắt buộc cho middleware auth của Laravel
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
Route::get('/security',    [TwoFactorController::class, 'securityPage'])->name('security')->middleware('auth');
Route::delete('/security/session/{id}', [TwoFactorController::class, 'logoutSession'])->name('security.session.destroy')->middleware('auth');


// Frontend
Route::get('/', function () {
    return redirect()->route('home');
});
Route::get('/Home', [HomeController::class, 'index'])->name('home');
Route::get('/san-pham/{id}', [ProductController::class, 'show'])->name('product.show');
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy')->middleware('auth');

// Modules
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/shoppingcart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('/ShippingCosts', [CartController::class, 'shipping'])->name('cart.shipping');
Route::get('/pay', [CartController::class, 'pay'])->name('cart.pay');
Route::post('/pay/wallet-points', [CartController::class, 'applyWalletPoints'])->name('cart.pay.wallet-points');
Route::post('/pay/place-order', [CartController::class, 'placeOrder'])->name('cart.place-order');
Route::get('/order-confirmation/{orderId}', [CartController::class, 'confirmation'])->name('cart.confirmation');
Route::get('/maQR', [CartController::class, 'ai'])->name('cart.qr');
Route::get('/orders', [CartController::class, 'tracking'])->name('cart.tracking');
Route::get('/print-bill', [CartController::class, 'print'])->name('cart.print');

Route::get('/rewards', [RewardsController::class, 'index'])->name('rewards.index');
Route::get('/rewards/{reward}', [RewardsController::class, 'show'])->name('rewards.show');
Route::get('/rewards/history', [RewardsHistoryController::class, 'index'])->name('rewards.history');
Route::post('/rewards/redeem', [RewardsController::class, 'redeem'])->name('rewards.redeem');
Route::post('/rewards/spin', [RewardsController::class, 'spin'])->name('rewards.spin');

// Articles & Lifestyle
Route::get('/lifestyle', [\App\Http\Controllers\ArticleFrontendController::class, 'index'])->name('articles.index');
Route::middleware('auth')->group(function() {
    Route::get('/lifestyle/create', [\App\Http\Controllers\ArticleFrontendController::class, 'create'])->name('articles.create');
    Route::post('/lifestyle/store', [\App\Http\Controllers\ArticleFrontendController::class, 'store'])->name('articles.store');
    Route::get('/lifestyle/{id}/edit', [\App\Http\Controllers\ArticleFrontendController::class, 'edit'])->name('articles.edit');
    Route::put('/lifestyle/{id}', [\App\Http\Controllers\ArticleFrontendController::class, 'update'])->name('articles.update');
    Route::delete('/lifestyle/{id}', [\App\Http\Controllers\ArticleFrontendController::class, 'destroy'])->name('articles.destroy');
});
Route::get('/lifestyle/{slug}', [\App\Http\Controllers\ArticleFrontendController::class, 'show'])->name('articles.show');

Route::match(['get', 'post'], '/admin/permissions', function () {
    return view('admin.permissions.index');
})->name('admin.permissions.index')->middleware([\App\Http\Middleware\IsAdmin::class]);

// Product Filtering
Route::get('/products', [App\Http\Controllers\Frontend\ProductController::class, 'index'])->name('products.index');
Route::get('/products/filter', [ProductFilterController::class, 'filterProducts'])->name('products.filter');
Route::get('/products/{categorySlug}', [App\Http\Controllers\Frontend\ProductController::class, 'index'])->name('products.category');
Route::get('/product/{id}', [App\Http\Controllers\Frontend\ProductController::class, 'show'])->name('product.detail');
Route::get('/api/categories/{id}/filters', [ProductFilterController::class, 'getCategoryFilters'])->name('api.categories.filters');

// Admin Customer Management
Route::prefix('admin')->middleware(['auth', \App\Http\Middleware\IsAdmin::class])->group(function () {
    Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class, ['as' => 'admin']);
    Route::get('rewards', [AdminRewardsController::class, 'index'])->name('admin.rewards.index');
    Route::post('rewards', [AdminRewardsController::class, 'store'])->name('admin.rewards.store');
    Route::put('rewards/{reward}', [AdminRewardsController::class, 'update'])->name('admin.rewards.update');
    Route::put('rewards/{reward}/image', [RewardImageController::class, 'update'])->name('admin.rewards.image.update');
    Route::delete('rewards/{reward}', [AdminRewardsController::class, 'destroy'])->name('admin.rewards.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    
    // Address management
    Route::post('/profile/address', [ProfileController::class, 'addAddress'])->name('profile.address.store');
    Route::post('/profile/address/{id}', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::delete('/profile/address/{id}', [ProfileController::class, 'deleteAddress'])->name('profile.address.destroy');
    
    Route::delete('/profile/wishlist/clear', [ProfileController::class, 'clearWishlist'])->name('profile.wishlist.clear');
    Route::delete('/profile/wishlist/{id}', [ProfileController::class, 'removeFromWishlist'])->name('profile.wishlist.destroy');
    Route::post('/wishlist/toggle', [ProfileController::class, 'toggleWishlist'])->name('wishlist.toggle');
});

// Product Compare (So sánh sản phẩm)
Route::get('/compare', [CompareController::class, 'index'])->name('compare.index');
Route::get('/compare/data', [CompareController::class, 'data'])->name('compare.data');
Route::post('/compare/sync', [CompareController::class, 'sync'])->name('compare.sync');
Route::get('/api/products/search-compare', [CompareController::class, 'searchCompare'])->name('api.products.search-compare');
