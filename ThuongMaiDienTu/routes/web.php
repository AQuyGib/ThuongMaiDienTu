<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CashbookController;


Route::match(['get', 'post'], '/login-register', function () {
    return view('Auth.login_register');
})->name('login_register');

Route::get('/auth/{provider}', [App\Http\Controllers\Auth\SocialController::class, 'redirectToProvider'])->name('social.login');
Route::match(['get', 'post'], '/users', function () {
    return view('PhanQuyen.user');
})->name('users.index');

Route::get('/', function () {
    return redirect('/Home');
});

Route::get('/Home', [HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::resource('cashbooks', CashbookController::class);

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');
