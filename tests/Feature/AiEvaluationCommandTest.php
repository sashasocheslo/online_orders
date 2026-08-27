<?php

use App\Enums\AiProvider;
use App\Services\Ai\AiEvaluationDataset;
use App\Services\Ai\AiProviderRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\Fakes\FakeAiProvider;
use Tests\Fixtures\AiRecommendationFixture;

beforeEach(function () {
    Http::preventStrayRequests();
});

function bindEvaluationFakeProvider(FakeAiProvider $provider): void
{
    $registry = Mockery::mock(AiProviderRegistry::class);
    $registry->shouldReceive('resolve')
        ->once()
        ->with($provider->provider())
        ->andReturn($provider);

    app()->instance(AiProviderRegistry::class, $registry);
}

test('evaluation dataset is fixed and internally valid', function () {
    $dataset = app(AiEvaluationDataset::class)->load();

    expect($dataset['version'])->toBe('1.0.0')
        ->and($dataset['products'])->toHaveCount(7)
        ->and($dataset['scenarios'])->toHaveCount(5);
});

test('evaluation command reports the offline baseline without network access', function () {
    $exitCode = Artisan::call('ai:evaluate');
    $output = Artisan::output();

    expect($exitCode)->toBe(0)
        ->and($output)->toContain('"method": "baseline"')
        ->and($output)->toContain('"scenario_count": 5')
        ->and($output)->toContain('"constraint_match_rate": 20')
        ->and($output)->toContain('"expected_match_rate": 20')
        ->and($output)->toContain('"ai": null');

    Http::assertNothingSent();
});

test('evaluation command blocks live providers without explicit network consent', function () {
    $this->artisan('ai:evaluate', ['--provider' => 'gemini'])
        ->expectsOutputToContain('Live AI-виклики заблоковано')
        ->assertFailed();

    Http::assertNothingSent();
});

test('evaluation command compares every scenario through a fake provider', function () {
    $provider = (new FakeAiProvider(AiProvider::Gemini))
        ->respondWith(AiRecommendationFixture::response('Бюджетні позиції.', [4, 5]))
        ->respondWith(AiRecommendationFixture::response('Вегетаріанська страва.', [3]))
        ->respondWith(AiRecommendationFixture::response('Гострий бургер.', [2]))
        ->respondWith(AiRecommendationFixture::response('Відповідних суші немає.', []))
        ->respondWith(AiRecommendationFixture::response('Сімейна піца.', [7]));
    bindEvaluationFakeProvider($provider);

    $exitCode = Artisan::call('ai:evaluate', [
        '--provider' => 'gemini',
        '--allow-network' => true,
    ]);
    $output = Artisan::output();

    expect($exitCode)->toBe(0)
        ->and($output)->toContain('"method": "gemini"')
        ->and($output)->toContain('"constraint_match_rate": 100')
        ->and($output)->toContain('"expected_match_rate": 100')
        ->and($output)->toContain('"expected_match_rate_difference": 80')
        ->and($output)->toContain('"total_external_calls": 5');

    expect($provider->calls)->toHaveCount(5)
        ->and($provider->calls[0]['system_prompt'])->toContain('Рекомендуй тільки товари')
        ->and($provider->calls[0]['user_prompt'])->toContain('Контрольне меню дипломного дослідження')
        ->and($provider->calls[0]['user_prompt'])->toContain('Порадь будь-яку страву до 100 грн.');

    Http::assertNothingSent();
});
