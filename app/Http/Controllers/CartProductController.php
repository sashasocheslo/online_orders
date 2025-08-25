<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CartProductServiceInterface;
use App\Models\CartProduct;

class CartProductController extends Controller
{
    private CartProductServiceInterface $cartService;

    public function __construct(CartProductServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $cartProducts = $this->cartService->listCartProducts();

        if ($request->wantsJson()) {
            return response()->json($cartProducts, 200);
        }

        return view('cart_product.index', ['cart_products' => $cartProducts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cart_id' => 'required|exists:carts,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $this->cartService->addProduct($validated['cart_id'], $validated['product_id']);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Product added to cart',
                'cart_id' => $validated['cart_id'],
                'product_id' => $validated['product_id'],
            ], 201);
        }

        return redirect()->back();
    }

    public function destroy(CartProduct $cartProduct, Request $request)
    {
        $this->cartService->removeProduct($cartProduct);

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->back();
    }
}
