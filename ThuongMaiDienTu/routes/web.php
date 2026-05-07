<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CashbookController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\ProfileController;

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

Route::get('/users', function () {
    return view('PhanQuyen.user');
})->name('users.index');

Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
Route::post('/profile/password/update', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
Route::post('/profile/address', [ProfileController::class, 'addAddress'])->name('profile.address.store');
Route::post('/profile/address/{id}', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
Route::delete('/profile/address/{id}', [ProfileController::class, 'deleteAddress'])->name('profile.address.destroy');
Route::delete('/profile/wishlist/{id}', [ProfileController::class, 'removeFromWishlist'])->name('profile.wishlist.destroy');
