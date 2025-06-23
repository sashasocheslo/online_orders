<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Client;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Menu $menu, Request $request)
    {
        $search = $request->input('search');
        $activeCategories = $request->input('categories');

        $products = $menu->products()
            ->when($search, fn($query) => $query->search($search))
            ->when($activeCategories, fn($query) => $query->categories(['categories' => $activeCategories]))
            ->get();

        $cart = $this->getOrCreateCart();

        return view('components.product.index', [
            'products' => $products,
            'cart' => $cart,
        ]);
    }

    private function getOrCreateCart()
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
}
