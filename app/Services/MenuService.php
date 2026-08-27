<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Menu;
use App\Services\Contracts\MenuServiceInterface;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Http\Request;

class MenuService implements MenuServiceInterface
{
    private ProductServiceInterface $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    public function getAllMenus()
    {
        return Menu::all();
    }

    public function getMenuDetails(Menu $menu, Request $request): array
    {
        $categories = Category::query()
            ->whereHas(
                'products',
                fn ($query) => $query->where('menu_id', $menu->id),
            )
            ->orderBy('id')
            ->get();

        $data = $this->productService->getProducts($menu, $request);

        return [
            'categories' => $categories,
            'products' => $data['products'],
        ];
    }
}
