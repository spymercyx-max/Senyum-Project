<?php

namespace App\Models;

use App\Enums\StockStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'stock',
        'reserved',
        'threshold',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'reserved' => 'integer',
            'threshold' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getAvailableAttribute(): int
    {
        return max(0, (int) $this->stock - (int) $this->reserved);
    }

    public function getStockStatusAttribute(): string
    {
        return app(\App\Services\InventoryService::class)->stockStatus($this);
    }

    public function stockStatusEnum(): StockStatus
    {
        return StockStatus::from($this->stock_status);
    }
}
