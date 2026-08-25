<?php

namespace App\Services\Ai;

use App\Enums\AiProvider;
use App\Services\Contracts\AiProviderInterface;

class AiProviderRegistry
{
    public function __construct(
        private readonly OpenAiProvider $openAi,
        private readonly GeminiProvider $gemini,
        private readonly ClaudeProvider $claude,
    ) {}

    public function resolve(AiProvider $provider): AiProviderInterface
    {
        return match ($provider) {
            AiProvider::OpenAi => $this->openAi,
            AiProvider::Gemini => $this->gemini,
            AiProvider::Claude => $this->claude,
        };
    }

    /**
     * @return list<AiProviderInterface>
     */
    public function all(): array
    {
        return [$this->openAi, $this->gemini, $this->claude];
    }
}
