<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'price', 'menu_id', 'image', 'description', 'category_id', 'size'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cartProducts(): HasMany
    {
        return $this->hasMany(CartProduct::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $query) use ($search) {
            $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhereHas('category', function (Builder $query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%');
                });
        });
    }

    public function scopeCategories(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['categories'] ?? null,
                fn (Builder $query, $activeCategories) => $query->whereIn('category_id', (array) $activeCategories)
            )
            ->when($filters['min_price'] ?? null,
                fn (Builder $query, $minPrice) => $query->where('price', '>=', $minPrice)
            )
            ->when($filters['max_price'] ?? null,
                fn (Builder $query, $maxPrice) => $query->where('price', '<=', $maxPrice)
            );
    }

    public function hasUserAdd(User $user): bool
    {
        return $this->cartProducts()
            ->whereHas(
                'cart',
                fn (Builder $query) => $query->where('user_id', $user->id),
            )
            ->exists();
    }
}
