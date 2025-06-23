<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Визначає чи користувач може переглядати будь-які продукти
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Визначає чи користувач може переглянути конкретний продукт
     */
    public function view(?User $user, Product $product): bool
    {
        return true;
    }

    /**
     * Визначає чи користувач може створити продукт
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Визначає чи користувач може оновити продукт
     */
    public function update(User $user, Product $product): bool
    {
        return false;
    }

    /**
     * Визначає чи користувач може видалити продукт
     */
    public function delete(User $user, Product $product): bool
    {
        return false;
    }

    /**
     * Визначає чи користувач може відновити продукт
     */
    public function restore(User $user, Product $product): bool
    {
        return false;
    }

    /**
     * Визначає чи користувач може повністю видалити продукт
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return false;
    }

    /**
     * Визначає чи користувач може додати продукт до кошика
     */
    public function add(User $user, Product $product): bool
    {
        return !$product->hasUserAdd($user);
    }
}
