<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OptionController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PrivacyRequestController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\QuoteOfferController;
use App\Http\Controllers\Admin\QuoteRequestController;
use App\Http\Controllers\BusinessPrintController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\InvoiceController as CustomerInvoiceController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Customer\QuoteController as CustomerQuoteController;
use App\Http\Controllers\Customer\VerificationController as CustomerVerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocalPaymentController;
use App\Http\Controllers\QuoteOfferAcceptanceController;
use App\Http\Middleware\OwnerMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
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
Route::post('/kontakt', [ContactMessageController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
Route::view('/polityka-prywatnosci', 'privacy')->name('privacy');
Route::redirect('/kontakt.html', '/kontakt', 301)->name('contact.legacy');

Route::view('/koszyk', 'cart')->name('cart');
Route::redirect('/koszyk.html', '/koszyk', 301)->name('cart.legacy');

Route::post('/zamowienie', [CheckoutController::class, 'store'])->middleware('throttle:orders')->name('checkout.store');
Route::get('/zamowienie/sukces/{token}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/wycena/{token}', [QuoteOfferAcceptanceController::class, 'show'])->name('quote-offers.show');
Route::post('/wycena/{token}/akceptuj', [QuoteOfferAcceptanceController::class, 'accept'])->middleware('throttle:orders')->name('quote-offers.accept');

if (app()->environment('local', 'testing')) {
    Route::get('/platnosc-testowa/{payment}', [LocalPaymentController::class, 'show'])->name('local-payments.show');
    Route::post('/platnosc-testowa/{payment}', [LocalPaymentController::class, 'store'])->name('local-payments.store');
}

Route::prefix('konto')->group(function (): void {
    Route::get('/logowanie', [CustomerAuthController::class, 'createLogin'])->name('customer.login');
    Route::post('/logowanie', [CustomerAuthController::class, 'login'])->middleware('throttle:login')->name('customer.login.store');
    Route::get('/rejestracja', [CustomerAuthController::class, 'createRegister'])->name('customer.register');
    Route::post('/rejestracja', [CustomerAuthController::class, 'register'])->middleware('throttle:login')->name('customer.register.store');
    Route::get('/reset-hasla', [CustomerAuthController::class, 'forgotPassword'])->name('customer.password.request');
    Route::post('/reset-hasla', [CustomerAuthController::class, 'sendResetLink'])->middleware('throttle:login')->name('customer.password.email');
    Route::get('/reset-hasla/{token}', [CustomerAuthController::class, 'resetPasswordForm'])->name('customer.password.reset');
    Route::post('/reset-hasla/zmien', [CustomerAuthController::class, 'resetPassword'])->middleware('throttle:login')->name('customer.password.update');
});

Route::prefix('konto')->middleware(['auth', 'customer'])->group(function (): void {
    Route::post('/wyloguj', [CustomerAuthController::class, 'logout'])->name('customer.logout');
    Route::get('/weryfikacja', [CustomerVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/weryfikacja/{id}/{hash}', [CustomerVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/weryfikacja/ponownie', [CustomerVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::prefix('konto')->name('customer.')->middleware(['auth', 'customer', 'verified'])->group(function (): void {
    Route::get('/', CustomerDashboardController::class)->name('dashboard');
    Route::get('/zamowienia', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/zamowienia/{orderNumber}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('/zamowienia/{orderNumber}/platnosc', [CustomerOrderController::class, 'retryPayment'])
        ->middleware('throttle:orders')
        ->name('orders.retry-payment');
    Route::get('/zamowienia/{orderNumber}/pliki/{file}', [CustomerOrderController::class, 'downloadFile'])->name('orders.files.download');
    Route::get('/wyceny', [CustomerQuoteController::class, 'index'])->name('quotes.index');
    Route::get('/wyceny/{reference}', [CustomerQuoteController::class, 'show'])->name('quotes.show');
    Route::post('/wyceny/{reference}/oferty/{offer}/akceptuj', [CustomerQuoteController::class, 'accept'])
        ->middleware('throttle:orders')
        ->name('quotes.accept');
    Route::get('/faktury/{invoice}', [CustomerInvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/profil', [CustomerProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [CustomerProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/haslo', [CustomerProfileController::class, 'password'])->name('profile.password');
});

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('dashboard')->name('admin.')->group(function (): void {
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'update'])->parameters(['orders' => 'order'])->names('orders');
        Route::resource('quotes', QuoteRequestController::class)->only(['index', 'show', 'update'])->parameters(['quotes' => 'quoteRequest'])->names('quote-requests');
        Route::post('/quotes/{quoteRequest}/offers', [QuoteOfferController::class, 'store'])->name('quote-offers.store');
        Route::get('/quotes/{quoteRequest}/files/{file}', [QuoteRequestController::class, 'downloadFile'])->scopeBindings()->name('quote-requests.files.download');
        Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('/orders/{order}/files/{file}', [OrderController::class, 'downloadFile'])->name('orders.files.download');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::put('/notifications/{notification}', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::put('/notifications', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

        Route::middleware(OwnerMiddleware::class)->group(function (): void {
            Route::resource('clients', ClientController::class)->only(['index', 'show'])->parameters(['clients' => 'client'])->names('clients');
            Route::get('/clients/{client}/export', [ClientController::class, 'export'])->name('clients.export');
            Route::post('/clients/{client}/deletion-request', [ClientController::class, 'requestDeletion'])->name('clients.deletion');
            Route::post('/clients/{client}/anonymize', [ClientController::class, 'anonymize'])->name('clients.anonymize');
            Route::resource('options', OptionController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])->parameters(['options' => 'option'])->names('options');
            Route::resource('products', AdminProductController::class)->only(['index', 'edit', 'update'])->parameters(['products' => 'product'])->names('products');
            Route::get('/privacy', [PrivacyRequestController::class, 'index'])->name('privacy.index');
            Route::put('/privacy/{dataRequest}', [PrivacyRequestController::class, 'update'])->name('privacy.update');
            Route::get('/exports/orders.csv', [ExportController::class, 'orders'])->name('exports.orders');
            Route::get('/exports/clients.csv', [ExportController::class, 'clients'])->name('exports.clients');
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
