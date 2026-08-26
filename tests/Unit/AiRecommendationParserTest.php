<?php

use App\Exceptions\InvalidAiResponseException;
use App\Services\Ai\AiRecommendationParser;
use Tests\Fixtures\AiRecommendationFixture;

test('AI recommendation parser accepts only the expected JSON contract', function () {
    $result = (new AiRecommendationParser)->parse(
        AiRecommendationFixture::response('Рекомендую бургер.', [3, 2, 3]),
    );

    expect($result->message)->toBe('Рекомендую бургер.')
        ->and($result->productIds)->toBe([3, 2]);
});

test('AI recommendation parser rejects malformed or unsafe payloads', function (string $payload) {
    expect(fn () => (new AiRecommendationParser)->parse($payload))
        ->toThrow(InvalidAiResponseException::class);
})->with([
    'plain text' => AiRecommendationFixture::malformedResponse(),
    'markdown fenced JSON' => '```json {"message":"ok","product_ids":[]} ```',
    'missing product IDs' => '{"message":"ok"}',
    'unexpected property' => AiRecommendationFixture::responseWithExtraField(),
    'string product ID' => '{"message":"ok","product_ids":["1"]}',
    'too many product IDs' => '{"message":"ok","product_ids":[1,2,3,4]}',
    'empty message' => '{"message":"","product_ids":[]}',
]);
