<?php

use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

pest()->use(RefreshDatabase::class);

test('user can register through the API and receives a hashed expiring token', function () {
    $response = $this->postJson(route('api.auth.register'), [
        'name' => 'API Test User',
        'email' => 'api-register@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'Pest registration',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.email', 'api-register@example.com')
        ->assertJsonPath('user.role', 'user')
        ->assertJsonMissingPath('user.password')
        ->assertJsonStructure([
            'token_type',
            'token',
            'expires_at',
            'user' => [
                'id',
                'name',
                'email',
                'role',
                'email_verified_at',
            ],
        ]);

    $plainTextToken = $response->json('token');
    expect($plainTextToken)->toBeString()->toContain('|');

    [$tokenId, $tokenSecret] = explode('|', $plainTextToken, 2);
    $storedToken = PersonalAccessToken::query()->findOrFail((int) $tokenId);

    expect($storedToken->token)->toBe(hash('sha256', $tokenSecret))
        ->not->toBe($tokenSecret)
        ->and($storedToken->name)->toBe('Pest registration')
        ->and($storedToken->abilities)->toBe(['api:access'])
        ->and($storedToken->expires_at)->not->toBeNull()
        ->and($storedToken->expires_at->isBetween(
            now()->addDays(6),
            now()->addDays(8),
        ))->toBeTrue();

    $this->assertDatabaseHas('users', [
        'email' => 'api-register@example.com',
    ]);
});

test('user can log in through the API with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'api-login@example.com',
        'password' => 'password123',
    ]);

    $response = $this->postJson(route('api.auth.login'), [
        'email' => $user->email,
        'password' => 'password123',
        'device_name' => 'Pest login',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonMissingPath('user.password');

    expect($response->json('token'))->toBeString()->toContain('|');
    $this->assertDatabaseCount('personal_access_tokens', 1);
});

test('API login rejects invalid credentials without creating a token', function () {
    $user = User::factory()->create([
        'password' => 'correct-password',
    ]);

    $this->postJson(route('api.auth.login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'Unknown device',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Невірні облікові дані.');

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('API registration and login validate their JSON payloads', function () {
    $this->postJson(route('api.auth.register'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'name',
            'email',
            'password',
            'device_name',
        ]);

    $this->postJson(route('api.auth.login'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'email',
            'password',
            'device_name',
        ]);
});

test('public catalog routes do not require an API token', function () {
    $menu = Menu::query()->create([
        'name' => 'Public API Menu',
        'image' => 'menus/public-api.png',
    ]);

    $this->getJson(route('api.menus.index'))
        ->assertOk();

    $this->getJson(route('api.menus.show', $menu))
        ->assertOk()
        ->assertJsonPath('menu.id', $menu->id);

    $this->getJson(route('api.menus.products.index', $menu))
        ->assertOk()
        ->assertJsonStructure(['products']);
});

test('protected API routes reject guests', function () {
    $this->getJson(route('api.auth.me'))
        ->assertUnauthorized();

    $this->getJson(route('api.cart-products.index'))
        ->assertUnauthorized();
});

test('token must have the api access ability', function () {
    $user = User::factory()->create();
    $wrongAbilityToken = $user->createToken('Wrong ability', ['profile:read']);

    $this->withToken($wrongAbilityToken->plainTextToken)
        ->getJson(route('api.auth.me'))
        ->assertForbidden();

    $this->app['auth']->forgetGuards();

    $correctToken = $user->createToken('Correct ability', ['api:access']);

    $this->withToken($correctToken->plainTextToken)
        ->getJson(route('api.auth.me'))
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonMissingPath('data.password');
});

test('API logout revokes only the current token', function () {
    $user = User::factory()->create();
    $firstToken = $user->createToken(
        'First device',
        ['api:access'],
        now()->addDays(7),
    );
    $secondToken = $user->createToken(
        'Second device',
        ['api:access'],
        now()->addDays(7),
    );

    $this->withToken($firstToken->plainTextToken)
        ->deleteJson(route('api.auth.logout'))
        ->assertNoContent();

    $this->app['auth']->forgetGuards();

    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $firstToken->accessToken->id,
    ]);
    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $secondToken->accessToken->id,
    ]);

    $this->withToken($firstToken->plainTextToken)
        ->getJson(route('api.auth.me'))
        ->assertUnauthorized();

    $this->app['auth']->forgetGuards();

    $this->withToken($secondToken->plainTextToken)
        ->getJson(route('api.auth.me'))
        ->assertOk()
        ->assertJsonPath('data.id', $user->id);
});
