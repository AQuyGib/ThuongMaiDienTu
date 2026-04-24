<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::match(['get', 'post'], '/login-register', function () {
    return view('Auth.login_register');
})->name('login_register');

Route::get('/auth/{provider}', [App\Http\Controllers\Auth\SocialController::class, 'redirectToProvider'])->name('social.login');
Route::get('/auth/{provider}/callback', [App\Http\Controllers\Auth\SocialController::class, 'handleProviderCallback']);

Route::match(['get', 'post'], '/users', function () {
    return view('PhanQuyen.user');
})->name('users.index');
