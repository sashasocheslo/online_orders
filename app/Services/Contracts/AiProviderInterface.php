<?php

namespace App\Services\Contracts;

use App\Data\AiConversationMessage;
use App\Enums\AiProvider;

interface AiProviderInterface
{
    public function provider(): AiProvider;

    public function configured(): bool;

    /**
     * @param  list<AiConversationMessage>  $history
     */
    public function generate(
        string $systemPrompt,
        string $userPrompt,
        array $history = [],
    ): string;
}
