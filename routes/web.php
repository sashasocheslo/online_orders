<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartProductController;
use App\Http\Controllers\CatalogSearchController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('', fn () => to_route('menu.index'));

Route::resource('menu', MenuController::class)
    ->only('index', 'show');

Route::get('/catalog/search', CatalogSearchController::class)
    ->name('catalog.search');

Route::post('/stripe/webhook', StripeWebhookController::class)
    ->name('stripe.webhook');

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

    Route::get('/menu/{menu}/orders/create', [OrderController::class, 'create'])
        ->name('menu.orders.create');

    Route::post('/menu/{menu}/orders', [OrderController::class, 'store'])
        ->name('menu.orders.store');

    Route::get('/orders', [OrderController::class, 'index'])
        ->name('orders.index');

    Route::get('/orders/{order}', [OrderController::class, 'show'])
        ->name('orders.show');

    Route::post('/orders/{order}/payment', [PaymentController::class, 'store'])
        ->name('orders.payment.store');

    Route::get('/orders/{order}/payment/return', [PaymentController::class, 'returnFromStripe'])
        ->name('orders.payment.return');
});

Route::controller(SocialiteController::class)->group(function () {
    Route::get('auth/google', 'googleLogin')
        ->name('auth.google');
    Route::get('auth/google-callback', 'googleAuthentication')->name('auth.google-callback');
});
