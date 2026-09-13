<?php

use App\Http\Controllers\Api\B2bQuoteRequestController;
use App\Http\Controllers\Api\InPostController;
use App\Http\Controllers\Api\OrderUploadController;
use App\Http\Controllers\Api\PayuWebhookController;
use App\Http\Controllers\Api\PdfOrderController;
use App\Http\Controllers\Api\ThesisQuoteController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('api.products.show');
Route::get('/inpost/points', [InPostController::class, 'points'])
    ->middleware('throttle:inpost')
    ->name('api.inpost.points');
Route::post('/orders', [CheckoutController::class, 'store'])->middleware('throttle:orders')->name('api.orders.store');
Route::post('/uploads', OrderUploadController::class)->middleware('throttle:orders')->name('api.uploads.store');
Route::post('/b2b/uploads', [B2bQuoteRequestController::class, 'upload'])->middleware('throttle:orders')->name('api.b2b.uploads.store');
Route::post('/b2b/quote-requests', [B2bQuoteRequestController::class, 'store'])->middleware('throttle:orders')->name('api.b2b.quote-requests.store');
Route::post('/thesis/quote', ThesisQuoteController::class)->middleware('throttle:orders')->name('api.thesis.quote');
Route::post('/thesis/orders', [CheckoutController::class, 'thesis'])->middleware('throttle:orders')->name('api.thesis.orders.store');
Route::post('/pdf/quote', [PdfOrderController::class, 'quote'])->middleware('throttle:orders')->name('api.pdf.quote');
Route::post('/pdf/orders', [PdfOrderController::class, 'store'])->middleware('throttle:orders')->name('api.pdf.orders.store');
Route::post('/payments/payu/notify', PayuWebhookController::class)->name('api.payments.payu.notify');
