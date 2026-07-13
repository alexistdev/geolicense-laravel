<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest / auth
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Auth::check()
        ? redirect(Auth::user()->role->homeUrl())
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:100,1');

    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequest'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:5,1')->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [Admin\UserController::class, 'index'])->name('users');

    Route::get('/products', [Admin\ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [Admin\ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [Admin\ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/license_types', [Admin\LicenseTypeController::class, 'index'])->name('license-types.index');
    Route::post('/license_types', [Admin\LicenseTypeController::class, 'store'])->name('license-types.store');
    Route::put('/license_types/{licenseType}', [Admin\LicenseTypeController::class, 'update'])->name('license-types.update');
    Route::delete('/license_types/{licenseType}', [Admin\LicenseTypeController::class, 'destroy'])->name('license-types.destroy');

    Route::get('/invoices', [Admin\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [Admin\InvoiceController::class, 'show'])->name('invoices.show');
    Route::patch('/invoices/{invoice}/validate', [Admin\InvoiceController::class, 'validateInvoice'])->name('invoices.validate');
    Route::patch('/invoices/{invoice}/reject', [Admin\InvoiceController::class, 'reject'])->name('invoices.reject');
    Route::patch('/invoices/{invoice}/void', [Admin\InvoiceController::class, 'void'])->name('invoices.void');

    Route::get('/settings', [Admin\SettingsController::class, 'edit'])->name('settings');
    Route::patch('/settings/profile', [Admin\SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::patch('/settings/password', [Admin\SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::patch('/settings/recaptcha', [Admin\SettingsController::class, 'updateRecaptcha'])->name('settings.recaptcha');
});

/*
|--------------------------------------------------------------------------
| User
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:USER'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [User\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/marketplace', [User\MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::get('/marketplace/{product}', [User\MarketplaceController::class, 'show'])->name('marketplace.show');

    Route::post('/orders', [User\OrderController::class, 'store'])->name('orders.store');

    Route::get('/invoice', [User\InvoiceController::class, 'index'])->name('invoice.index');
    Route::get('/invoice/{invoice}', [User\InvoiceController::class, 'show'])->name('invoice.show');
    Route::get('/invoice/{invoice}/payment', [User\InvoiceController::class, 'payment'])->name('invoice.payment');
    Route::post('/invoice/{invoice}/payment', [User\InvoiceController::class, 'submitPayment'])->name('invoice.payment.submit');

    Route::get('/license', [User\LicenseController::class, 'index'])->name('license.index');
    Route::get('/license/{license}', [User\LicenseController::class, 'show'])->name('license.show');
});
