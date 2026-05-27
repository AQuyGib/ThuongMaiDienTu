<?php

use App\Http\Controllers\Api\AttributeController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Middleware\ResolveApiLocale;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware([ResolveApiLocale::class])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    Route::get('/pages', [PageController::class, 'index']);
    Route::get('/pages/{page}', [PageController::class, 'show']);

    Route::get('/attributes', [AttributeController::class, 'index']);
    Route::get('/attributes/{attribute}', [AttributeController::class, 'show']);
});
