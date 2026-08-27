<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('menu_id')
                ->nullable()
                ->after('user_id')
                ->constrained('menus')
                ->cascadeOnDelete();
        });

        DB::transaction(function (): void {
            $carts = DB::table('carts')
                ->orderBy('id')
                ->get();

            foreach ($carts as $cart) {
                $menuIds = DB::table('cart_products')
                    ->join('products', 'products.id', '=', 'cart_products.product_id')
                    ->where('cart_products.cart_id', $cart->id)
                    ->distinct()
                    ->orderBy('products.menu_id')
                    ->pluck('products.menu_id');

                if ($menuIds->isEmpty()) {
                    DB::table('carts')->where('id', $cart->id)->delete();

                    continue;
                }

                foreach ($menuIds as $index => $menuId) {
                    if ($index === 0) {
                        $targetCartId = $cart->id;

                        DB::table('carts')
                            ->where('id', $targetCartId)
                            ->update(['menu_id' => $menuId]);
                    } else {
                        $targetCartId = DB::table('carts')->insertGetId([
                            'user_id' => $cart->user_id,
                            'menu_id' => $menuId,
                            'created_at' => $cart->created_at,
                            'updated_at' => $cart->updated_at,
                        ]);
                    }

                    DB::table('cart_products')
                        ->where('cart_id', $cart->id)
                        ->whereIn(
                            'product_id',
                            DB::table('products')
                                ->select('id')
                                ->where('menu_id', $menuId),
                        )
                        ->update(['cart_id' => $targetCartId]);
                }
            }

            $duplicateCarts = DB::table('carts')
                ->select('user_id', 'menu_id')
                ->whereNotNull('user_id')
                ->whereNotNull('menu_id')
                ->groupBy('user_id', 'menu_id')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicateCarts as $duplicateCart) {
                $cartIds = DB::table('carts')
                    ->where('user_id', $duplicateCart->user_id)
                    ->where('menu_id', $duplicateCart->menu_id)
                    ->orderBy('id')
                    ->pluck('id');

                $targetCartId = $cartIds->shift();

                DB::table('cart_products')
                    ->whereIn('cart_id', $cartIds)
                    ->update(['cart_id' => $targetCartId]);

                DB::table('carts')
                    ->whereIn('id', $cartIds)
                    ->delete();
            }

            $duplicateProducts = DB::table('cart_products')
                ->select('cart_id', 'product_id')
                ->groupBy('cart_id', 'product_id')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicateProducts as $duplicateProduct) {
                $cartProducts = DB::table('cart_products')
                    ->where('cart_id', $duplicateProduct->cart_id)
                    ->where('product_id', $duplicateProduct->product_id)
                    ->orderBy('id')
                    ->get();

                $targetCartProduct = $cartProducts->shift();
                $quantity = $targetCartProduct->quantity + $cartProducts->sum('quantity');

                DB::table('cart_products')
                    ->where('id', $targetCartProduct->id)
                    ->update(['quantity' => $quantity]);

                DB::table('cart_products')
                    ->whereIn('id', $cartProducts->pluck('id'))
                    ->delete();
            }
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id')
                ->nullable(false)
                ->change();

            $table->unique(
                ['user_id', 'menu_id'],
                'carts_user_id_menu_id_unique',
            );
        });

        Schema::table('cart_products', function (Blueprint $table) {
            $table->unique(
                ['cart_id', 'product_id'],
                'cart_products_cart_id_product_id_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_products', function (Blueprint $table) {
            $table->dropUnique('cart_products_cart_id_product_id_unique');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique('carts_user_id_menu_id_unique');
        });

        DB::transaction(function (): void {
            $userIds = DB::table('carts')
                ->whereNotNull('user_id')
                ->distinct()
                ->pluck('user_id');

            foreach ($userIds as $userId) {
                $cartIds = DB::table('carts')
                    ->where('user_id', $userId)
                    ->orderBy('id')
                    ->pluck('id');

                if ($cartIds->count() < 2) {
                    continue;
                }

                $targetCartId = $cartIds->shift();

                DB::table('cart_products')
                    ->whereIn('cart_id', $cartIds)
                    ->update(['cart_id' => $targetCartId]);

                DB::table('carts')
                    ->whereIn('id', $cartIds)
                    ->delete();
            }
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('menu_id');
        });
    }
};
