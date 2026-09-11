<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'outlet_id',
        'product_id',
        'qty',
        'sold_qty',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'sold_qty' => 'integer',
            'status' => 'string',
        ];
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getRemainingAttribute(): int
    {
        return max(0, (int) $this->qty - (int) $this->sold_qty);
    }
}
