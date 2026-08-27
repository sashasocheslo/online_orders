<?php

namespace App\Data;

use App\Enums\AiProvider;

final readonly class AiAnswer
{
    /**
     * @param  array<int, array{id: int, name: string, price: string, image_url: string, url: string}>  $products
     */
    public function __construct(
        public AiProvider $provider,
        public string $text,
        public array $products = [],
    ) {}

    /**
     * @return array{
     *     provider: string,
     *     provider_label: string,
     *     answer: string,
     *     products: array<int, array{id: int, name: string, price: string, image_url: string, url: string}>
     * }
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider->value,
            'provider_label' => $this->provider->label(),
            'answer' => $this->text,
            'products' => $this->products,
        ];
    }
}
