<?php

namespace App\Data;

final readonly class AiRecommendationResult
{
    /**
     * @param  list<int>  $productIds
     */
    public function __construct(
        public string $message,
        public array $productIds,
    ) {}
}
