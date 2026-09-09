<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('api.products.show');
Route::post('/orders', [CheckoutController::class, 'store'])->middleware('throttle:orders')->name('api.orders.store');
