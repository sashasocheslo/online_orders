<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = ['user_id', 'menu_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function cartProducts(): HasMany
    {
        return $this->hasMany(CartProduct::class);
    }

    public function subtotal(): float
    {
        $subtotalInCents = $this->cartProducts->sum(
            fn (CartProduct $cartProduct): int => (int) round((float) $cartProduct->product->price * 100)
                * $cartProduct->quantity,
        );

        return $subtotalInCents / 100;
    }
}
