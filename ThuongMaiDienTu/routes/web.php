<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;


use App\Http\Controllers\Auth\AuthController;

Route::get('/login-register', [AuthController::class, 'index'])->name('login_register');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::get('/auth/{provider}', [App\Http\Controllers\Auth\SocialController::class, 'redirectToProvider'])->name('social.login');
Route::get('/users', function () {
    return view('PhanQuyen.user');
})->name('users.index');

Route::get('/', function () {
    return redirect()->route('home');
});
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/auth/{provider}/callback', [App\Http\Controllers\Auth\SocialController::class, 'handleProviderCallback']);

// Giỏ hàng dùng chung cho cả khách và admin (truy cập qua /shoppingcart)
use App\Http\Controllers\Admin\CartController;
Route::get('/shoppingcart', [CartController::class, 'index'])->name('cart.index');
Route::get('/ShippingCosts', [CartController::class, 'shipping'])->name('cart.shipping');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
