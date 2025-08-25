<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartProductController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

Route::get('', fn() =>to_route('menu.index'));

Route::resource('menu', MenuController::class)
    ->only('index', 'show');

Route::delete('auth', [AuthController::class, 'destroy'])
    ->name('auth.destroy');

Route::resource('menu.products', ProductController::class)
    ->only(['create', 'store', 'edit', 'update', 'destroy']);

Route::post('/menu/{menu}/products/{product}/comments', [ProductController::class, 'storeComment'])
    ->name('products.comments.store');

Route::delete('/comments/{comment}', [ProductController::class, 'destroyComment'])
    ->name('comments.destroy');



Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

Route::resource('cart_product', CartProductController::class)
    ->only('index', 'destroy', 'store');


Route::get('/order/create', [StripeController::class, 'create'])->name('order.create');

Route::post('/stripe/payment', [StripeController::class, 'payment'])->name('stripe.payment');

// Після успішної оплати
Route::get('/stripe/payment/success', [StripeController::class, 'success'])->name('stripe.payment.success');

Route::controller(SocialiteController::class)->group(function() {
    Route::get('auth/google', 'googleLogin')
        ->name('auth.google');
    Route::get('auth/google-callback', 'googleAuthentication')->name('auth.google-callback');
});

