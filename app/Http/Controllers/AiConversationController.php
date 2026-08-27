<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Services\Contracts\AiConversationServiceInterface;
use Illuminate\Http\JsonResponse;

class AiConversationController extends Controller
{
    public function __construct(
        private readonly AiConversationServiceInterface $conversations,
    ) {}

    public function destroy(Menu $menu): JsonResponse
    {
        $this->conversations->clear($menu);

        return response()->json(null, 204);
    }
}
