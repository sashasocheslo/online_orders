<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use App\Services\Contracts\OrderServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private OrderServiceInterface $orderService,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Order::class);

        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return view('orders.index', [
            'orders' => $this->orderService->listForUser($user),
        ]);
    }

    public function create(Menu $menu, Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return view('orders.create', [
            'menu' => $menu,
            'cart' => $this->orderService->getCheckoutCart($user, $menu),
        ]);
    }

    public function store(Menu $menu, StoreOrderRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $order = $this->orderService->createFromCart(
            $user,
            $menu,
            $request->validated(),
        );

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Замовлення створено. Перевірте його перед оплатою.');
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        return view('orders.show', [
            'order' => $this->orderService->loadDetails($order),
        ]);
    }
}
