<?php

namespace App\Services\Ai;

final class AiRecommendationSchema
{
    /**
     * @return array<string, mixed>
     */
    public static function definition(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'message' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 500,
                ],
                'product_ids' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'integer',
                    ],
                    'maxItems' => 3,
                ],
            ],
            'required' => ['message', 'product_ids'],
            'additionalProperties' => false,
        ];
    }
}
