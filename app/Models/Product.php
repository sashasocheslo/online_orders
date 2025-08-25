<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'menu_id', 'image', 'description', 'category_id', 'size'];
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    public function menu() : BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function category() : BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cart_products() : HasMany
    {
        return $this->hasMany(CartProduct::class);
    }

    public function comments() : HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeSearch(Builder|QueryBuilder $query, string $search) : Builder|QueryBuilder
    {
        return $query->where(function($query) use($search) {
            $query->where('name', 'like', '%' . $search . '%')
              ->orWhere('description', 'like', '%' . $search .'%')
              ->orWhereHas('category', function ($query) use($search) {
                    $query->where('name', 'like', '%' . $search . '%');
              });
        });
    }

   public function scopeCategories(Builder|QueryBuilder $query, array $filters)
    {
        return $query
            ->when($filters['categories'] ?? null,
                fn ($query, $activeCategories) =>
                    $query->whereIn('category_id', (array) $activeCategories)
            )
            ->when($filters['min_price'] ?? null,
                fn ($query, $minPrice) =>
                    $query->where('price', '>=', $minPrice)
            )
            ->when($filters['max_price'] ?? null,
                fn ($query, $maxPrice) =>
                    $query->where('price', '<=', $maxPrice)
            );
    }


    public function hasUserAdd(User $user): bool
    {
        return $this->cart_products()
            ->where('cart_id', $user->cart?->id ?? 0)
            ->exists();
    }
}
