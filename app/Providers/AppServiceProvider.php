<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Comment;
use App\Models\Order;
use App\Models\Product;
use App\Policies\CartPolicy;
use App\Policies\CartProductPolicy;
use App\Policies\CommentPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Services\Ai\AiAssistantService;
use App\Services\Ai\AiConversationService;
use App\Services\ApiTokenService;
use App\Services\AuthService;
use App\Services\CartProductService;
use App\Services\Contracts\AiAssistantServiceInterface;
use App\Services\Contracts\AiConversationServiceInterface;
use App\Services\Contracts\ApiTokenServiceInterface;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\CartProductServiceInterface;
use App\Services\Contracts\MenuServiceInterface;
use App\Services\Contracts\OrderServiceInterface;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\Contracts\PaymentServiceInterface;
use App\Services\Contracts\ProductServiceInterface;
use App\Services\Contracts\SocialiteServiceInterface;
use App\Services\MenuService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\ProductService;
use App\Services\SocialiteService;
use App\Services\StripeService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiAssistantServiceInterface::class, AiAssistantService::class);
        $this->app->bind(AiConversationServiceInterface::class, AiConversationService::class);
        $this->app->bind(ApiTokenServiceInterface::class, ApiTokenService::class);
        $this->app->bind(PaymentGatewayInterface::class, StripeService::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(CartProductServiceInterface::class, CartProductService::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(MenuServiceInterface::class, MenuService::class);
        $this->app->bind(SocialiteServiceInterface::class, SocialiteService::class);
    }

    public function boot(): void
    {
        RateLimiter::for('ai-recommendations', function (Request $request) {
            $user = $request->user();

            return Limit::perMinute(5)->by(
                $user === null
                    ? $request->ip()
                    : $user->getAuthIdentifier().'|'.mb_strtolower($user->email),
            );
        });

        Gate::policy(Cart::class, CartPolicy::class);
        Gate::policy(CartProduct::class, CartProductPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
    }
}
