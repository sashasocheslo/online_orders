<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\User;
use App\Services\Contracts\CartProductServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CartProductController extends Controller
{
    private CartProductServiceInterface $cartService;

    public function __construct(CartProductServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $cartProducts = $this->cartService->listCartProducts($user);

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

        $cart = Cart::query()->findOrFail($validated['cart_id']);
        Gate::authorize('update', $cart);

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
        Gate::authorize('update', $cartProduct);

        $this->cartService->removeProduct($cartProduct);

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->back();
    }
}
