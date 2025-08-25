<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Services\MenuServiceInterface;

class MenuController extends Controller
{
    private MenuServiceInterface $menuService;

    public function __construct(MenuServiceInterface $menuService)
    {
        $this->menuService = $menuService;
    }

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
                'cart' => $data['cart'],
            ], 200);
        }

        $view = match ($menu->name) {
            "McDonald's" => 'menu_mac.show',
            'KFC' => 'menu_kfc.show',
            'Domino\'s Pizza' => 'menu_pizza.show',
            default => 'menu.show',
        };

        return view($view, [
            'menu' => $menu,
            'categories' => $data['categories'],
            'products' => $data['products'],
            'cart' => $data['cart'],
        ]);
    }
}
