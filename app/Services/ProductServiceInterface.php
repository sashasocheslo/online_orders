<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Prompts\Prompt;


interface ProductServiceInterface
{

    public function getProducts(Menu $menu, Request $request): array;

    public function getOrCreateCart();

    public function createProduct(Menu $menu): array;

    public function storeProduct(Menu $menu, Request $request);

    public function editProduct(Menu $menu, Product $product): array;

    public function updateProduct(Menu $menu, Product $product, Request $request);

    public function deleteProduct(Menu $menu, Product $product): bool;

    public function addComment(Product $product, Request $request): Comment;

    public function getComments(Product $product): Collection;

    public function deleteComment(Comment $comment): bool;

}
