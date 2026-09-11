<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'distributor_id',
        'outlet_id',
        'buyer_name',
        'status',
        'type',
        'total_amount',
        'notes',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'type' => 'string',
            'total_amount' => 'integer',
            'sold_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
