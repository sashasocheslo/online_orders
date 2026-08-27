<?php

namespace App\Services\Contracts;

use App\Enums\AiProvider;

interface AiProviderInterface
{
    public function provider(): AiProvider;

    public function configured(): bool;

    public function generate(string $systemPrompt, string $userPrompt): string;
}
