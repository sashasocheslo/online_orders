<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\View\ViewException;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::query()->get();
        return view('menu.index', ['menus' => $menus]);
    }

    public function show(Menu $menu, Request $request)
    {
        $categories = Category::query()->get();

        $productController = new \App\Http\Controllers\ProductController();
        $productMethod = $productController->index($menu, $request);

        $data = $productMethod->getData();

        $manus = match ($menu->name) {
            "McDonald's" => 'menu_mac.show',
            'KFC' => 'menu_kfc.show',
            'Domino\'s Pizza' => 'menu_pizza.show',
        };

        return view($manus, [
            'menu' => $menu,
            'categories' => $categories,
            'products' => $data['products'],
            'cart' => $data['cart'],
        ]);
    }

}
