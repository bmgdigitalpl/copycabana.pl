<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\OptionController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PrivacyRequestController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\QuoteOfferController;
use App\Http\Controllers\Admin\QuoteRequestController;
use App\Http\Controllers\BusinessPrintController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\QuoteOfferAcceptanceController;
use App\Http\Middleware\OwnerMiddleware;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::redirect('/index.html', '/', 301)->name('home.legacy');
Route::view('/prace-dyplomowe', 'prace-dyplomowe')->name('services.diploma');
Route::view('/druk-pdf', 'druk-pdf')->name('druk-pdf');
Route::get('/druk-dla-firm', BusinessPrintController::class)->name('services.business');

Route::view('/brand', 'brand')->name('brand');
Route::redirect('/design-system', '/brand', 301)->name('brand.design-system');
Route::view('/test/fonts', 'test.fonts')->name('test.fonts');

Route::permanentRedirect('/produkty', '/druk-dla-firm')->name('products.legacy');
Route::permanentRedirect('/produkty.html', '/druk-dla-firm')->name('products.html.legacy');
Route::permanentRedirect('/produkt', '/druk-dla-firm')->name('product.legacy');
Route::permanentRedirect('/produkt.html', '/druk-dla-firm')->name('product.html.legacy');

Route::view('/o-nas', 'about')->name('about');
Route::redirect('/o-nas.html', '/o-nas', 301)->name('about.legacy');

Route::view('/kontakt', 'contact')->name('contact');
Route::view('/polityka-prywatnosci', 'privacy')->name('privacy');
Route::redirect('/kontakt.html', '/kontakt', 301)->name('contact.legacy');

Route::view('/koszyk', 'cart')->name('cart');
Route::redirect('/koszyk.html', '/koszyk', 301)->name('cart.legacy');

Route::post('/zamowienie', [CheckoutController::class, 'store'])->middleware('throttle:orders')->name('checkout.store');
Route::get('/zamowienie/sukces/{token}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/wycena/{token}', [QuoteOfferAcceptanceController::class, 'show'])->name('quote-offers.show');
Route::post('/wycena/{token}/akceptuj', [QuoteOfferAcceptanceController::class, 'accept'])->middleware('throttle:orders')->name('quote-offers.accept');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/logowanie', [AuthController::class, 'create'])->name('login');
    Route::post('/logowanie', [AuthController::class, 'store'])->middleware('throttle:login')->name('login.store');
    Route::post('/wyloguj', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::resource('zamowienia', OrderController::class)->only(['index', 'show', 'update'])->parameters(['zamowienia' => 'order'])->names('orders');
        Route::resource('wyceny', QuoteRequestController::class)->only(['index', 'show', 'update'])->parameters(['wyceny' => 'quoteRequest'])->names('quote-requests');
        Route::post('/wyceny/{quoteRequest}/oferty', [QuoteOfferController::class, 'store'])->name('quote-offers.store');
        Route::get('/wyceny/{quoteRequest}/pliki/{file}', [QuoteRequestController::class, 'downloadFile'])->scopeBindings()->name('quote-requests.files.download');
        Route::get('/zamowienia/{order}/faktura', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('/zamowienia/{order}/pliki/{file}', [OrderController::class, 'downloadFile'])->name('orders.files.download');
        Route::middleware(OwnerMiddleware::class)->group(function (): void {
            Route::resource('klienci', ClientController::class)->only(['index', 'show'])->parameters(['klienci' => 'client'])->names('clients');
            Route::get('/klienci/{client}/eksport', [ClientController::class, 'export'])->name('clients.export');
            Route::post('/klienci/{client}/wniosek-usuniecia', [ClientController::class, 'requestDeletion'])->name('clients.deletion');
            Route::post('/klienci/{client}/anonimizuj', [ClientController::class, 'anonymize'])->name('clients.anonymize');
            Route::resource('opcje', OptionController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->parameters(['opcje' => 'option'])->names('options');
            Route::resource('produkty', AdminProductController::class)->only(['index', 'edit', 'update'])->parameters(['produkty' => 'product'])->names('products');
            Route::get('/rodo', [PrivacyRequestController::class, 'index'])->name('privacy.index');
            Route::put('/rodo/{dataRequest}', [PrivacyRequestController::class, 'update'])->name('privacy.update');
            Route::get('/eksport/zamowienia.csv', [ExportController::class, 'orders'])->name('exports.orders');
            Route::get('/eksport/klienci.csv', [ExportController::class, 'clients'])->name('exports.clients');
        });
    });
});

Route::view('/realizacje', 'portfolio')->name('portfolio');
Route::view('/faq', 'faq')->name('faq');
Route::view('/dostawa-i-odbior', 'delivery')->name('delivery');

Route::prefix('archive')->group(function (): void {
    Route::view('/', 'archive')->name('archive');
    Route::view('/strona-glowna', 'archive.home')->name('archive.home');
    Route::view('/oprawa-prac', 'archive.services.diploma')->name('archive.diploma');
    Route::view('/dla-firm', 'archive.services.business')->name('archive.business');
});

// Old duplicated URLs → new production slugs.
Route::redirect('/oprawa-prac-dyplomowych-katowice', '/prace-dyplomowe', 301)->name('services.diploma.legacy');
Route::redirect('/dla-firm', '/druk-dla-firm', 301)->name('services.business.legacy');

// Concept playground shorts (kept as redirects for now).
Route::redirect('/concept/strona', '/', 301)->name('concept.home');
Route::redirect('/concept/strona/prace-dyplomowe', '/prace-dyplomowe', 301)->name('concept.thesis');
Route::redirect('/concept/strona/druk-pdf', '/druk-pdf', 301)->name('concept.pdf');
Route::redirect('/concept/strona/druk-dla-firm', '/druk-dla-firm', 301)->name('concept.b2b');
