<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return redirect('/Home');
});

Route::get('/Home', [HomeController::class, 'index'])->name('home');
