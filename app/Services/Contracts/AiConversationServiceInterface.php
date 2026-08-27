<?php

namespace App\Services\Contracts;

use App\Data\AiConversationMessage;
use App\Models\Menu;

interface AiConversationServiceInterface
{
    /**
     * @return list<AiConversationMessage>
     */
    public function history(Menu $menu): array;

    public function rememberExchange(Menu $menu, string $question, string $answer): void;

    public function clear(Menu $menu): void;
}
