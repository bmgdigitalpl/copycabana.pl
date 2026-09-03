<?php

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::redirect('/index.html', '/', 301)->name('home.legacy');

Route::view('/produkty', 'products')->name('products.index');
Route::redirect('/produkty.html', '/produkty', 301)->name('products.legacy');

Route::view('/produkt', 'product')->name('product');
Route::get('/produkt.html', function (Request $request): RedirectResponse {
    return redirect()->route('product', $request->query(), 301);
})->name('product.legacy');

Route::view('/o-nas', 'about')->name('about');
Route::redirect('/o-nas.html', '/o-nas', 301)->name('about.legacy');

Route::view('/kontakt', 'contact')->name('contact');
Route::redirect('/kontakt.html', '/kontakt', 301)->name('contact.legacy');

Route::view('/koszyk', 'cart')->name('cart');
Route::redirect('/koszyk.html', '/koszyk', 301)->name('cart.legacy');
