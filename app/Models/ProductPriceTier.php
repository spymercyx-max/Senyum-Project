<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPriceTier extends Model
{
    use HasFactory;

    public const CHANNEL_CUSTOMER = 'customer';
    public const CHANNEL_DISTRIBUTOR = 'distributor';

    protected $fillable = [
        'product_id',
        'channel',
        'min_qty',
        'max_qty',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'channel' => 'string',
            'min_qty' => 'integer',
            'max_qty' => 'integer',
            'price' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function matches(int $qty): bool
    {
        if ($qty < $this->min_qty) {
            return false;
        }
        if ($this->max_qty !== null && $qty > $this->max_qty) {
            return false;
        }
        return true;
    }
}
