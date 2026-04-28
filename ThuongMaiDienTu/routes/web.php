<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;


Route::match(['get', 'post'], '/login-register', function () {
    return view('Auth.login_register');
})->name('login_register');

Route::get('/auth/{provider}', [App\Http\Controllers\Auth\SocialController::class, 'redirectToProvider'])->name('social.login');
Route::match(['get', 'post'], '/users', function () {
    return view('PhanQuyen.user');
})->name('users.index');

Route::get('/', function () {
    return redirect()->route('home');
});
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/auth/{provider}/callback', [App\Http\Controllers\Auth\SocialController::class, 'handleProviderCallback']);

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');
