<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'price',
        'distributor_price',
        'sku',
        'image',
        'status',
        'featured',
        'sort_order',
        'ingredients',
        'availability_note',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'distributor_price' => 'integer',
            'status' => 'string',
            'featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class)->orderBy('channel')->orderBy('min_qty');
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function isAvailable(int $qty = 1): bool
    {
        $inventory = $this->relationLoaded('inventory') ? $this->inventory : $this->inventory()->first();

        if (! $inventory) {
            return false;
        }

        if ($this->status !== 'active') {
            return false;
        }

        return $inventory->available >= $qty;
    }
}
