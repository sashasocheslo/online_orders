<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Services\ProductServiceInterface;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private ProductServiceInterface $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    public function index(Menu $menu, Request $request)
    {
        $data = $this->productService->getProducts($menu, $request);

        if ($request->wantsJson()) {
            return response()->json([
                'products' => $data['products'],
                'cart' => $data['cart'],
            ], 200);
        }

        return view('components.product.index', [
            'products' => $data['products'],
            'cart' => $data['cart'],
        ]);
    }

    public function create(Menu $menu, Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Not supported for API. Use POST /menus/{menu}/products'
            ], 405);
        }

        $data = $this->productService->createProduct($menu);
        return view('products.create', $data);
    }

    public function store(Menu $menu, Request $request)
    {
        $this->productService->storeProduct($menu, $request);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Product created'], 201);
        }

        return redirect()->route('menu.show', $menu)->with('success', 'Продукт додано!');
    }

    public function edit(Menu $menu, Product $product, Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Not supported for API. Use PUT/PATCH /menus/{menu}/products/{product}'
            ], 405);
        }

        $data = $this->productService->editProduct($menu, $product);
        return view('products.edit', $data);
    }

    public function update(Menu $menu, Product $product, Request $request)
    {
        $this->productService->updateProduct($menu, $product, $request);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Product updated'], 200);
        }

        return redirect()->route('menu.show', $menu)->with('success', 'Продукт оновлено!');
    }

    public function storeComment(Menu $menu, Product $product, Request $request)
    {
        $this->productService->addComment($product, $request);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Comment added'], 201);
        }

        return redirect()->back()->with('success', 'Коментар додано!');
    }

    public function destroyComment(Comment $comment, Request $request)
    {
        if ($this->productService->deleteComment($comment)) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Comment deleted'], 200);
            }
            return back()->with('success', 'Коментар видалено');
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return back()->with('error', 'Ви не можете видалити цей коментар');
    }

    public function destroy(Menu $menu, Product $product, Request $request)
    {
        $this->productService->deleteProduct($menu, $product);

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('menu.show', $menu)->with('success', 'Продукт видалено!');
    }
}
