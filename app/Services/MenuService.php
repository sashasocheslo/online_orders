<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Category;
use App\Http\Controllers\ProductController;
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
        $categories = Category::all();

        $data = $this->productService->getProducts($menu, $request);

        return [
            'categories' => $categories,
            'products' => $data['products'],
            'cart' => $data['cart'],
        ];
    }
}
