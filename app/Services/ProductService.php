<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Client;
use App\Models\Comment;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
            ->when($search, fn($query) => $query->search($search))
            ->categories($filters)
            ->orderBy('price', 'asc')
            ->with(['comments.user'])
            ->get();

        $cart = $this->getOrCreateCart();

        return [
            'products' => $products,
            'cart' => $cart,
        ];
    }

    public function getOrCreateCart()
    {
        $user = Auth::user();

        if ($user) {
            return $user->cart ?? Cart::create(['user_id' => $user->id]);
        } else {
            $client = Client::find(session('client_id')) ?? Client::create();
            session(['client_id' => $client->id]);
            return null;
        }
    }

    public function createProduct(Menu $menu): array
    {
        $categories = Category::all();

        return [
            'menu' => $menu,
            'categories' => $categories,
        ];
    }


    public function storeProduct(Menu $menu, Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $path = $request->file('image')->store('products', 'public');

        return $menu->products()->create([
            'name'        => $validated['name'],
            'price'       => $validated['price'],
            'image'       => $path,
            'category_id' => $validated['category_id'] ?? null,
        ]);
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

    public function updateProduct(Menu $menu, Product $product, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return $product;
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
        dd($comment);
    }

    public function getComments(Product $product): Collection
    {
        return $product->comments()->with('user')->latest()->get();
    }

    public function deleteComment(Comment $comment): bool
    {
        if (Auth::check() && Auth::id() === $comment->user_id) {
            return $comment->delete();
        }

        return false;
    }
    public function deleteProduct(Menu $menu, Product $product): bool
    {
        // Проверка на принадлежность к меню (безопасность)
        if ($product->menu_id !== $menu->id) {
            abort(403, 'Видалення заборонено');
        }

        return $product->delete();
    }
}
