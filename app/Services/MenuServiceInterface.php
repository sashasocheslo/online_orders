<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Http\Request;


interface MenuServiceInterface
{
    /**
     * Отримання всіх меню
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllMenus();

    /**
     * Отримання продуктів та категорій для конкретного меню
     *
     * @param Menu $menu
     * @param Request $request
     * @return array ['categories' => ..., 'products' => ..., 'cart' => ...]
     */
    public function getMenuDetails(Menu $menu, Request $request): array;
}
