<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartProductRequest;
use App\Http\Requests\UpdateCartProductRequest;
use App\Models\CartProduct;
use App\Models\Menu;
use App\Models\Product;
use App\Models\User;
use App\Services\Contracts\CartProductServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CartProductController extends Controller
{
    private CartProductServiceInterface $cartService;

    public function __construct(CartProductServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $carts = $this->cartService->listCarts($user);

        return response()->json($carts, 200);
    }

    public function showForMenu(Menu $menu, Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $cart = $this->cartService->findCart($user, $menu);

        return view('cart_product.index', [
            'menu' => $menu,
            'cart' => $cart,
        ]);
    }

    public function store(StoreCartProductRequest $request)
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $product = Product::query()->findOrFail(
            $request->integer('product_id'),
        );

        $cartProduct = $this->cartService->addProduct($user, $product);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Product added to cart',
                'cart_product' => $cartProduct,
            ], 201);
        }

        return back()->with('success', 'Товар додано до кошика.');
    }

    public function update(
        CartProduct $cartProduct,
        UpdateCartProductRequest $request,
    ) {
        $cartProduct = $this->cartService->updateQuantity(
            $cartProduct,
            $request->integer('quantity'),
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Cart quantity updated',
                'cart_product' => $cartProduct,
            ], 200);
        }

        return back()->with('success', 'Кількість товару оновлено.');
    }

    public function destroy(CartProduct $cartProduct, Request $request)
    {
        Gate::authorize('delete', $cartProduct);

        $this->cartService->removeProduct($cartProduct);

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return back()->with('success', 'Товар видалено з кошика.');
    }
}
