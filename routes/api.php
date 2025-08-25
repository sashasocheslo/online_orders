<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartProductController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

// ---- Меню ----
Route::apiResource('menus', MenuController::class)
    ->only(['index', 'show']);

// ---- Продукти (вкладені у меню) ----
Route::apiResource('menus.products', ProductController::class)
    ->only(['index', 'store', 'update', 'destroy']);

// ---- Коментарі ----
Route::apiResource('products.comments', ProductController::class)
    ->only(['index', 'store', 'destroy']);

// ---- Аутентифікація (API) ----
Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::delete('/auth/logout', [AuthController::class, 'destroy'])->name('auth.destroy');

// ---- Кошик ----
Route::apiResource('cart-products', CartProductController::class)
    ->only(['index', 'store', 'destroy']);

// ---- Stripe (оплата API) ----
Route::post('/payments', [StripeController::class, 'payment'])->name('payments.store');
