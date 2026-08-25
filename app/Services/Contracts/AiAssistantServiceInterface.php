<?php

namespace App\Services\Contracts;

use App\Data\AiAnswer;
use App\Enums\AiProvider;
use App\Models\Menu;

interface AiAssistantServiceInterface
{
    /**
     * @return list<AiProvider>
     */
    public function availableProviders(): array;

    public function recommend(Menu $menu, AiProvider $provider, string $question): AiAnswer;
}
