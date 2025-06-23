<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartProduct extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'image'];

    public function cart() : BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

}
