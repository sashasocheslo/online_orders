<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartProductController;
use App\Http\Controllers\CatalogSearchController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

Route::get('', fn () => to_route('menu.index'));

Route::resource('menu', MenuController::class)
    ->only('index', 'show');

Route::get('/catalog/search', CatalogSearchController::class)
    ->name('catalog.search');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::delete('auth', [AuthController::class, 'destroy'])
        ->name('auth.destroy');

    Route::resource('menu.products', ProductController::class)
        ->only(['create', 'store', 'edit', 'update', 'destroy'])
        ->scoped();

    Route::post('/menu/{menu}/products/{product}/comments', [ProductController::class, 'storeComment'])
        ->scopeBindings()
        ->name('products.comments.store');

    Route::delete('/comments/{comment}', [ProductController::class, 'destroyComment'])
        ->name('comments.destroy');

    Route::get('/menu/{menu}/cart', [CartProductController::class, 'showForMenu'])
        ->name('menu.cart.index');

    Route::resource('cart_product', CartProductController::class)
        ->only(['store', 'update', 'destroy']);

    Route::get('/order/create', [StripeController::class, 'create'])
        ->name('order.create');

    Route::post('/stripe/payment', [StripeController::class, 'payment'])
        ->name('stripe.payment');

    Route::get('/stripe/payment/success', [StripeController::class, 'success'])
        ->name('stripe.payment.success');
});

Route::controller(SocialiteController::class)->group(function () {
    Route::get('auth/google', 'googleLogin')
        ->name('auth.google');
    Route::get('auth/google-callback', 'googleAuthentication')->name('auth.google-callback');
});
