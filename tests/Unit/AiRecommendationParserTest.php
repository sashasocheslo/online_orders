<?php

use App\Exceptions\InvalidAiResponseException;
use App\Services\Ai\AiRecommendationParser;

test('AI recommendation parser accepts only the expected JSON contract', function () {
    $result = (new AiRecommendationParser)->parse(json_encode([
        'message' => 'Рекомендую бургер.',
        'product_ids' => [3, 2, 3],
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

    expect($result->message)->toBe('Рекомендую бургер.')
        ->and($result->productIds)->toBe([3, 2]);
});

test('AI recommendation parser rejects malformed or unsafe payloads', function (string $payload) {
    expect(fn () => (new AiRecommendationParser)->parse($payload))
        ->toThrow(InvalidAiResponseException::class);
})->with([
    'plain text' => 'Рекомендую бургер.',
    'markdown fenced JSON' => '```json {"message":"ok","product_ids":[]} ```',
    'missing product IDs' => '{"message":"ok"}',
    'unexpected property' => '{"message":"ok","product_ids":[],"secret":"value"}',
    'string product ID' => '{"message":"ok","product_ids":["1"]}',
    'too many product IDs' => '{"message":"ok","product_ids":[1,2,3,4]}',
    'empty message' => '{"message":"","product_ids":[]}',
]);
