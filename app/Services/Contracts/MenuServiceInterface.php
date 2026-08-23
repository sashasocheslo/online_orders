<?php

namespace App\Services\Contracts;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface MenuServiceInterface
{
    /**
     * Отримання всіх меню.
     *
     * @return Collection
     */
    public function getAllMenus();

    /**
     * Отримання продуктів та категорій для конкретного меню.
     *
     * @return array ['categories' => ..., 'products' => ...]
     */
    public function getMenuDetails(Menu $menu, Request $request): array;
}
