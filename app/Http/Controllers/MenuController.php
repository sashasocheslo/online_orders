<?php

namespace App\Http\Controllers;

use App\Data\AiConversationMessage;
use App\Enums\AiProvider;
use App\Models\Menu;
use App\Services\Contracts\AiAssistantServiceInterface;
use App\Services\Contracts\AiConversationServiceInterface;
use App\Services\Contracts\MenuServiceInterface;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        private readonly MenuServiceInterface $menuService,
        private readonly AiAssistantServiceInterface $aiAssistant,
        private readonly AiConversationServiceInterface $aiConversation,
    ) {}

    public function index(Request $request)
    {
        $menus = $this->menuService->getAllMenus();

        if ($request->wantsJson()) {
            return response()->json($menus, 200);
        }

        return view('menu.index', ['menus' => $menus]);
    }

    public function show(Menu $menu, Request $request)
    {
        $data = $this->menuService->getMenuDetails($menu, $request);

        if ($request->wantsJson()) {
            return response()->json([
                'menu' => $menu,
                'categories' => $data['categories'],
                'products' => $data['products'],
            ], 200);
        }

        $view = match ($menu->name) {
            "McDonald's" => 'menu_mac.show',
            'KFC' => 'menu_kfc.show',
            'Domino\'s Pizza' => 'menu_pizza.show',
            default => 'menu.show',
        };

        $availableAiProviders = array_map(
            fn (AiProvider $provider): string => $provider->value,
            $this->aiAssistant->availableProviders(),
        );

        return view($view, [
            'menu' => $menu,
            'categories' => $data['categories'],
            'products' => $data['products'],
            'aiProviders' => AiProvider::cases(),
            'availableAiProviders' => $availableAiProviders,
            'aiConversation' => $request->user() === null
                ? []
                : array_map(
                    fn (AiConversationMessage $message): array => $message->toArray(),
                    $this->aiConversation->history($menu),
                ),
        ]);
    }
}
