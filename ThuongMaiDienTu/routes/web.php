<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CashbookController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\ProductFilterController;

// Authentication
Route::get('/login-register', [AuthController::class, 'index'])->name('login_register');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Social Login
Route::get('/auth/{provider}', [SocialController::class, 'redirectToProvider'])->name('social.login');
Route::get('/auth/{provider}/callback', [SocialController::class, 'handleProviderCallback']);

// Frontend
Route::get('/', function () {
    return redirect()->route('home');
});
Route::get('/Home', [HomeController::class, 'index'])->name('home');

// Modules
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('cashbooks', CashbookController::class);
Route::get('/shoppingcart', [CartController::class, 'index'])->name('cart.index');
Route::get('/ShippingCosts', [CartController::class, 'shipping'])->name('cart.shipping');

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

Route::get('/users', function () {
    return view('PhanQuyen.user');
})->name('users.index');

// Product Filtering
Route::get('/products', [App\Http\Controllers\Frontend\ProductController::class, 'index'])->name('products.index');
Route::get('/products/filter', [ProductFilterController::class, 'filterProducts'])->name('products.filter');
Route::get('/products/{categorySlug}', [App\Http\Controllers\Frontend\ProductController::class, 'index'])->name('products.category');
Route::get('/product/{id}', [App\Http\Controllers\Frontend\ProductController::class, 'show'])->name('product.detail');
Route::get('/api/categories/{id}/filters', [ProductFilterController::class, 'getCategoryFilters'])->name('api.categories.filters');
