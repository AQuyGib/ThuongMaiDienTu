<?php

use Illuminate\Support\Facades\Route;


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

Route::get('/home', function () {
    return view('home');
})->name('home');
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');
