<?php

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\CartProductController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('register', [ApiAuthController::class, 'register'])
            ->middleware('throttle:5,1')
            ->name('register');

        Route::post('login', [ApiAuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('login');
    });

    Route::apiResource('menus', MenuController::class)
        ->only(['index', 'show']);

    Route::apiResource('menus.products', ProductController::class)
        ->only(['index']);

    Route::middleware(['auth:sanctum', 'abilities:api:access'])->group(function () {
        Route::get('auth/me', [ApiAuthController::class, 'me'])
            ->name('auth.me');

        Route::delete('auth/logout', [ApiAuthController::class, 'logout'])
            ->name('auth.logout');

        Route::apiResource('cart-products', CartProductController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::apiResource('menus.products', ProductController::class)
            ->only(['store', 'update', 'destroy'])
            ->scoped();

        Route::post(
            'menus/{menu}/products/{product}/comments',
            [ProductController::class, 'storeComment'],
        )
            ->scopeBindings()
            ->name('products.comments.store');

        Route::delete('comments/{comment}', [ProductController::class, 'destroyComment'])
            ->name('comments.destroy');
    });
});
