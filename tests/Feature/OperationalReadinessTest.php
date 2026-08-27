<?php

use App\Models\Category;
use App\Models\Menu;
use App\Models\Product;
use App\Models\User;
use App\Services\Contracts\SocialiteServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

pest()->use(RefreshDatabase::class);

test('responses contain a unique request ID and baseline security headers', function () {
    $first = $this->get('/up')
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'same-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    $second = $this->get('/up')->assertOk();

    $firstRequestId = (string) $first->headers->get('X-Request-ID');
    $secondRequestId = (string) $second->headers->get('X-Request-ID');

    expect(Str::isUuid($firstRequestId))->toBeTrue()
        ->and(Str::isUuid($secondRequestId))->toBeTrue()
        ->and($secondRequestId)->not->toBe($firstRequestId);
});

test('slow requests are logged with safe metadata and a request ID', function () {
    config(['logging.slow_request_ms' => 0]);
    Log::spy();

    $this->get(route('menu.index'))->assertOk();

    Log::shouldHaveReceived('withContext')
        ->withArgs(fn (array $context): bool => isset($context['request_id'])
            && Str::isUuid((string) $context['request_id']));
    Log::shouldHaveReceived('warning')
        ->withArgs(function (string $message, array $context): bool {
            $sensitiveKeys = ['password', 'token', 'authorization', 'cookie', 'email'];

            return $message === 'Slow HTTP request detected.'
                && $context['method'] === 'GET'
                && $context['route_name'] === 'menu.index'
                && $context['status_code'] === 200
                && $context['duration_ms'] >= 0
                && array_intersect($sensitiveKeys, array_keys($context)) === [];
        });
});

test('web authentication routes are rate limited', function () {
    expect(Route::getRoutes()->getByName('login.store')?->gatherMiddleware())
        ->toContain('throttle:web-auth')
        ->and(Route::getRoutes()->getByName('register.store')?->gatherMiddleware())
        ->toContain('throttle:web-auth');

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->postJson(route('login.store'), [
            'email' => 'missing-user@example.test',
            'password' => 'not-a-real-password',
        ])->assertUnauthorized();
    }

    $this->postJson(route('login.store'), [
        'email' => 'missing-user@example.test',
        'password' => 'not-a-real-password',
    ])->assertTooManyRequests();
});

test('new Google users never receive the old predictable password', function () {
    $driver = Mockery::mock();
    $driver->shouldReceive('user')->once()->andReturn(SocialiteUser::fake([
        'id' => 'google-operational-test',
        'name' => 'Google Test User',
        'email' => 'google-operational@example.test',
    ]));
    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($driver);

    $user = app(SocialiteServiceInterface::class)->loginWithGoogle();

    expect($user)->toBeInstanceOf(User::class)
        ->and($user?->google_id)->toBe('google-operational-test')
        ->and(Hash::check('password', (string) $user?->password))->toBeFalse()
        ->and(auth()->id())->toBe($user?->id);
});

test('Google OAuth failures log only the exception type', function () {
    Log::spy();
    $driver = Mockery::mock();
    $driver->shouldReceive('user')->once()->andThrow(
        new RuntimeException('secret provider diagnostic'),
    );
    Socialite::shouldReceive('driver')->once()->with('google')->andReturn($driver);

    $user = app(SocialiteServiceInterface::class)->loginWithGoogle();

    expect($user)->toBeNull();
    Log::shouldHaveReceived('warning')->once()->with(
        'Google OAuth authentication failed.',
        ['exception' => RuntimeException::class],
    );
});

test('menu page keeps a stable query budget and shows only its categories', function () {
    Menu::query()->create(['name' => 'Перший ресторан', 'image' => 'first.png']);
    Menu::query()->create(['name' => 'Другий ресторан', 'image' => 'second.png']);
    $menu = Menu::query()->create(['name' => 'KFC', 'image' => 'kfc.png']);
    $otherMenu = Menu::query()->create(['name' => 'Інше меню', 'image' => 'other.png']);
    $commentAuthor = User::factory()->create();
    $otherCategory = Category::query()->create(['name' => 'Чужа категорія']);

    Product::query()->create([
        'name' => 'Чужий товар',
        'price' => 10,
        'description' => 'Не належить KFC.',
        'image' => 'other-product.png',
        'menu_id' => $otherMenu->id,
        'category_id' => $otherCategory->id,
    ]);

    foreach (range(1, 4) as $number) {
        $category = Category::query()->create(['name' => "KFC категорія {$number}"]);
        $product = Product::query()->create([
            'name' => "KFC товар {$number}",
            'price' => 50 + $number,
            'description' => 'Контрольний товар.',
            'image' => "kfc-product-{$number}.png",
            'menu_id' => $menu->id,
            'category_id' => $category->id,
        ]);
        $product->comments()->create([
            'user_id' => $commentAuthor->id,
            'content' => "Коментар {$number}",
        ]);
    }

    $queryCount = 0;
    DB::listen(function () use (&$queryCount): void {
        $queryCount++;
    });

    $this->get(route('menu.show', $menu))
        ->assertOk()
        ->assertSee('KFC категорія 1')
        ->assertDontSee('Чужа категорія');

    expect($queryCount)->toBeLessThanOrEqual(7);
});
