<?php

namespace App\Providers;

use App\Models\Product;
use App\Policies\ProductPolicy;
use App\Services\AuthService;
use App\Services\AuthServiceInterface;
use App\Services\CartProductService;
use App\Services\CartProductServiceInterface;
use App\Services\MenuService;
use App\Services\MenuServiceInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Services\StripeService;
use App\Services\PaymentGatewayInterface;
use App\Services\OrderService;
use App\Services\OrderServiceInterface;
use App\Services\ProductService;
use App\Services\ProductServiceInterface;
use App\Services\SocialiteService;
use App\Services\SocialiteServiceInterface;
use Laravel\Socialite\SocialiteServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, StripeService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(CartProductServiceInterface::class, CartProductService::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(MenuServiceInterface::class, MenuService::class);
        $this->app->bind(SocialiteServiceInterface::class, SocialiteService::class);
    }


    public function boot(): void
    {
        Gate::policy(Product::class, ProductPolicy::class);
    }

}
