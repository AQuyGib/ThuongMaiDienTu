<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CashbookController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('welcome');
})->name('dashboard');

Route::resource('cashbooks', CashbookController::class);