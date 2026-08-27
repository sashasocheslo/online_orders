<?php

namespace App\Services\Contracts;

use App\Models\Comment;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface ProductServiceInterface
{
    public function getProducts(Menu $menu, Request $request): array;

    public function createProduct(Menu $menu): array;

    public function storeProduct(Menu $menu, array $data): Product;

    public function editProduct(Menu $menu, Product $product): array;

    public function updateProduct(Menu $menu, Product $product, array $data): Product;

    public function deleteProduct(Menu $menu, Product $product): bool;

    public function addComment(Product $product, Request $request): Comment;

    public function getComments(Product $product): Collection;

    public function deleteComment(Comment $comment): bool;
}
