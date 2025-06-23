<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class CartProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Product $product)
    {
        Gate::authorize('add', $product);
        return view('cart_product.index',
        ['cart_products' => CartProduct::query()->get()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Product $product, Request $request)
    {
        Gate::authorize('add', $product);

        $validated = $request->validate([
            'cart_id' => 'required|exists:carts,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $cartProduct = CartProduct::where('cart_id', $validated['cart_id'])
        ->where('product_id', $validated['product_id'])
        ->first();

        if ($cartProduct) {
            $cartProduct->increment('quantity');
        } else {

            $product = Product::findOrFail($validated['product_id']);

            \App\Models\CartProduct::create([
                'cart_id' => $validated['cart_id'],
                'product_id' => $validated['product_id'],
                'image' => $product->image,
                'quantity' => 1,
            ]);
        }
        return redirect()->back();
    }

    public function destroy(CartProduct $cartProduct, Product $product)
    {
        Gate::authorize('add', $product);
        if ($cartProduct->quantity > 1) {
            $cartProduct->decrement('quantity');
        } else {
            $cartProduct->delete();
        }

        return redirect()->back();
    }
}
