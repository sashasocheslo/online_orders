<?php

namespace App\Services\Ai;

use App\Data\AiRecommendationResult;
use App\Exceptions\InvalidAiResponseException;
use JsonException;

final class AiRecommendationParser
{
    public function parse(string $answer): AiRecommendationResult
    {
        try {
            $decoded = json_decode(trim($answer), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidAiResponseException(
                'AI повернув некоректний JSON.',
                previous: $exception,
            );
        }

        if (! is_array($decoded) || array_is_list($decoded)) {
            throw new InvalidAiResponseException('AI-відповідь повинна бути JSON-об’єктом.');
        }

        $keys = array_keys($decoded);
        sort($keys);

        if ($keys !== ['message', 'product_ids']) {
            throw new InvalidAiResponseException('AI-відповідь не відповідає дозволеній схемі.');
        }

        $message = $decoded['message'];
        $productIds = $decoded['product_ids'];

        if (! is_string($message) || trim($message) === '' || mb_strlen(trim($message)) > 500) {
            throw new InvalidAiResponseException('AI повернув некоректне пояснення.');
        }

        if (! is_array($productIds) || ! array_is_list($productIds) || count($productIds) > 3) {
            throw new InvalidAiResponseException('AI повернув некоректний список товарів.');
        }

        foreach ($productIds as $productId) {
            if (! is_int($productId) || $productId < 1) {
                throw new InvalidAiResponseException('AI повернув некоректний ID товару.');
            }
        }

        return new AiRecommendationResult(
            message: trim($message),
            productIds: array_values(array_unique($productIds)),
        );
    }
}
