<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Menu;
use App\Models\Product;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductService implements ProductServiceInterface
{
    public function getProducts(Menu $menu, Request $request): array
    {
        $search = $request->input('search');

        // Збираємо фільтри в один масив
        $filters = [
            'categories' => $request->input('categories'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
        ];

        $products = $menu->products()
            ->when($search, fn ($query) => $query->search($search))
            ->categories($filters)
            ->orderBy('price', 'asc')
            ->with(['comments.user'])
            ->get();

        return [
            'products' => $products,
        ];
    }

    public function searchCatalog(array $filters): array
    {
        $productsQuery = Product::query()
            ->with(['menu', 'category'])
            ->when(
                $filters['query'] ?? null,
                fn ($query, $search) => $query->search($search),
            )
            ->when(
                $filters['menu_id'] ?? null,
                fn ($query, $menuId) => $query->where('menu_id', $menuId),
            )
            ->when(
                $filters['category_id'] ?? null,
                fn ($query, $categoryId) => $query->where('category_id', $categoryId),
            )
            ->when(
                array_key_exists('min_price', $filters) && $filters['min_price'] !== null,
                fn ($query) => $query->where('price', '>=', $filters['min_price']),
            )
            ->when(
                array_key_exists('max_price', $filters) && $filters['max_price'] !== null,
                fn ($query) => $query->where('price', '<=', $filters['max_price']),
            );

        $productsQuery = match ($filters['sort'] ?? 'price_asc') {
            'price_desc' => $productsQuery->orderByDesc('price'),
            'name' => $productsQuery->orderBy('name'),
            default => $productsQuery->orderBy('price'),
        };

        return [
            'products' => $productsQuery
                ->paginate(12)
                ->withQueryString(),
            'menus' => Menu::query()
                ->orderBy('name')
                ->get(),
            'categories' => Category::query()
                ->whereHas('products')
                ->orderBy('name')
                ->get(),
        ];
    }

    public function createProduct(Menu $menu): array
    {
        $categories = Category::all();

        return [
            'menu' => $menu,
            'categories' => $categories,
        ];
    }

    public function storeProduct(Menu $menu, array $data): Product
    {
        /** @var UploadedFile $image */
        $image = $data['image'];
        $data['image'] = $image->store('products', 'public');

        try {
            return $menu->products()->create($data);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($data['image']);

            throw $exception;
        }
    }

    public function editProduct(Menu $menu, Product $product): array
    {
        $categories = Category::all();

        return [
            'menu' => $menu,
            'product' => $product,
            'categories' => $categories,
        ];
    }

    public function updateProduct(Menu $menu, Product $product, array $data): Product
    {
        abort_unless($product->menu_id === $menu->id, 404);

        $oldImage = $product->image;
        $newImage = null;

        if (($data['image'] ?? null) instanceof UploadedFile) {
            $newImage = $data['image']->store('products', 'public');
            $data['image'] = $newImage;
        } else {
            unset($data['image']);
        }

        try {
            $product->update($data);
        } catch (Throwable $exception) {
            if ($newImage !== null) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        if ($newImage !== null && $oldImage !== null) {
            Storage::disk('public')->delete($oldImage);
        }

        return $product->refresh();
    }

    public function addComment(Product $product, Request $request): Comment
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        return $product->comments()->create([
            'content' => $validated['content'],
            'user_id' => Auth::id(),
        ]);
    }

    public function getComments(Product $product): Collection
    {
        return $product->comments()->with('user')->latest()->get();
    }

    public function deleteComment(Comment $comment): bool
    {
        return $comment->delete();
    }

    public function deleteProduct(Menu $menu, Product $product): bool
    {
        abort_unless($product->menu_id === $menu->id, 404);

        $image = $product->image;
        $deleted = $product->delete();

        if ($deleted && $image !== null) {
            Storage::disk('public')->delete($image);
        }

        return $deleted;
    }
}
