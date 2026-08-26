<?php

use App\Data\AiConversationMessage;
use App\Enums\AiProvider;
use App\Exceptions\AiProviderException;
use App\Models\Menu;
use App\Models\User;
use App\Services\Ai\AiProviderRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Fakes\FakeAiProvider;
use Tests\Fixtures\AiRecommendationFixture;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
});

function bindFakeAiProvider(FakeAiProvider $provider): void
{
    $registry = Mockery::mock(AiProviderRegistry::class);
    $registry->shouldReceive('resolve')
        ->with($provider->provider())
        ->andReturn($provider);

    app()->instance(AiProviderRegistry::class, $registry);
}

test('fake provider returns verified recommendations without an HTTP request', function () {
    $user = User::factory()->create();
    ['menu' => $menu, 'product' => $product] = AiRecommendationFixture::catalog();

    $provider = (new FakeAiProvider)
        ->respondWith(AiRecommendationFixture::response(
            'Fake provider радить тестовий бургер.',
            [$product->id],
        ));
    bindFakeAiProvider($provider);

    $this->actingAs($user)
        ->postJson(route('menu.ai.recommendations', $menu), [
            'provider' => 'gemini',
            'question' => 'Порадь ситний бургер',
        ])
        ->assertOk()
        ->assertJsonPath('fallback', false)
        ->assertJsonPath('answer', 'Fake provider радить тестовий бургер.')
        ->assertJsonPath('products.0.id', $product->id);

    expect($provider->calls)->toHaveCount(1)
        ->and($provider->calls[0]['system_prompt'])->toContain('Рекомендуй тільки товари')
        ->and($provider->calls[0]['user_prompt'])->toContain($menu->name)
        ->and($provider->calls[0]['user_prompt'])->toContain($product->name)
        ->and($provider->calls[0]['user_prompt'])->toContain('Порадь ситний бургер')
        ->and($provider->calls[0]['history'])->toBe([]);

    Http::assertNothingSent();
});

test('fake provider receives successful conversation history on a follow-up request', function () {
    $user = User::factory()->create();
    ['menu' => $menu] = AiRecommendationFixture::catalog();

    $provider = (new FakeAiProvider)
        ->respondWith(AiRecommendationFixture::response('Перша рекомендація.'))
        ->respondWith(AiRecommendationFixture::response('Дешевша рекомендація.'));
    bindFakeAiProvider($provider);

    $this->actingAs($user)
        ->postJson(route('menu.ai.recommendations', $menu), [
            'provider' => 'gemini',
            'question' => 'Порадь основну страву',
        ])
        ->assertOk();

    $this->actingAs($user)
        ->postJson(route('menu.ai.recommendations', $menu), [
            'provider' => 'gemini',
            'question' => 'А щось дешевше?',
        ])
        ->assertOk()
        ->assertJsonPath('answer', 'Дешевша рекомендація.');

    expect($provider->calls)->toHaveCount(2)
        ->and($provider->calls[1]['history'])->toHaveCount(2)
        ->and($provider->calls[1]['history'][0])->toBeInstanceOf(AiConversationMessage::class)
        ->and($provider->calls[1]['history'][0]->role)->toBe('user')
        ->and($provider->calls[1]['history'][0]->content)->toBe('Порадь основну страву')
        ->and($provider->calls[1]['history'][1]->role)->toBe('assistant')
        ->and($provider->calls[1]['history'][1]->content)->toBe('Перша рекомендація.');

    Http::assertNothingSent();
});

test('invalid fake responses use local fallback and are not remembered', function (string $response) {
    $user = User::factory()->create();
    ['menu' => $menu, 'product' => $product] = AiRecommendationFixture::catalog();

    $provider = (new FakeAiProvider)->respondWith($response);
    bindFakeAiProvider($provider);

    $sessionKey = "ai.conversations.user.{$user->id}.menu.{$menu->id}";

    $this->actingAs($user)
        ->postJson(route('menu.ai.recommendations', $menu), [
            'provider' => 'gemini',
            'question' => 'Порадь щось на випадок помилки',
        ])
        ->assertOk()
        ->assertJsonPath('fallback', true)
        ->assertJsonPath('products.0.id', $product->id)
        ->assertSessionMissing($sessionKey);

    expect($provider->calls)->toHaveCount(1);
    Http::assertNothingSent();
})->with([
    'malformed JSON' => [AiRecommendationFixture::malformedResponse()],
    'unexpected field' => [AiRecommendationFixture::responseWithExtraField()],
]);

test('temporary fake provider failure uses a safe local fallback', function () {
    $user = User::factory()->create();
    ['menu' => $menu, 'product' => $product] = AiRecommendationFixture::catalog();

    $provider = (new FakeAiProvider)->failWith(
        new AiProviderException('Внутрішня діагностика provider.'),
    );
    bindFakeAiProvider($provider);

    $this->actingAs($user)
        ->postJson(route('menu.ai.recommendations', $menu), [
            'provider' => 'gemini',
            'question' => 'Що обрати?',
        ])
        ->assertOk()
        ->assertJsonPath('fallback', true)
        ->assertJsonPath('products.0.id', $product->id)
        ->assertJsonMissing(['message' => 'Внутрішня діагностика provider.']);

    expect($provider->calls)->toHaveCount(1);
    Http::assertNothingSent();
});

test('permanent fake provider failure is returned without fallback', function () {
    $user = User::factory()->create();
    ['menu' => $menu] = AiRecommendationFixture::catalog();

    $provider = (new FakeAiProvider)->failWith(
        new AiProviderException(
            'Тестова помилка конфігурації.',
            fallbackAllowed: false,
        ),
    );
    bindFakeAiProvider($provider);

    $this->actingAs($user)
        ->postJson(route('menu.ai.recommendations', $menu), [
            'provider' => 'gemini',
            'question' => 'Що обрати?',
        ])
        ->assertServiceUnavailable()
        ->assertJsonPath('message', 'Тестова помилка конфігурації.')
        ->assertJsonMissingPath('fallback');

    expect($provider->calls)->toHaveCount(1);
    Http::assertNothingSent();
});

test('unconfigured fake provider stops before generation', function () {
    $user = User::factory()->create();
    ['menu' => $menu] = AiRecommendationFixture::catalog();

    $provider = new FakeAiProvider(AiProvider::Gemini, false);
    bindFakeAiProvider($provider);

    $this->actingAs($user)
        ->postJson(route('menu.ai.recommendations', $menu), [
            'provider' => 'gemini',
            'question' => 'Що обрати?',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Gemini поки не налаштовано.');

    expect($provider->calls)->toBe([]);
    Http::assertNothingSent();
});

test('unknown product IDs from fake provider never create product cards', function () {
    $user = User::factory()->create();
    ['menu' => $menu, 'product' => $product] = AiRecommendationFixture::catalog();

    $provider = (new FakeAiProvider)->respondWith(
        AiRecommendationFixture::response(
            'Відома й невідома позиції.',
            [$product->id, 999999],
        ),
    );
    bindFakeAiProvider($provider);

    $this->actingAs($user)
        ->postJson(route('menu.ai.recommendations', $menu), [
            'provider' => 'gemini',
            'question' => 'Покажи картки',
        ])
        ->assertOk()
        ->assertJsonPath('fallback', false)
        ->assertJsonCount(1, 'products')
        ->assertJsonPath('products.0.id', $product->id);

    Http::assertNothingSent();
});

test('empty menu stops before fake provider generation', function () {
    $user = User::factory()->create();
    $menu = Menu::query()->create([
        'name' => 'Порожній ресторан',
        'image' => 'menus/empty-ai-test.png',
    ]);

    $provider = new FakeAiProvider(AiProvider::Gemini);
    bindFakeAiProvider($provider);

    $this->actingAs($user)
        ->postJson(route('menu.ai.recommendations', $menu), [
            'provider' => 'gemini',
            'question' => 'Що обрати?',
        ])
        ->assertServiceUnavailable()
        ->assertJsonPath('message', 'У цьому ресторані поки немає товарів для рекомендації.');

    expect($provider->calls)->toBe([]);
    Http::assertNothingSent();
});
