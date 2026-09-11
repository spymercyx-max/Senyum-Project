<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'distributor_id',
        'status',
        'fulfillment',
        'order_date',
        'payment_proof',
        'tracking_number',
        'delivery_note',
        'pickup_message',
        'rejection_reason',
        'notes',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'fulfillment' => 'string',
            'order_date' => 'date',
            'total_amount' => 'integer',
        ];
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(PoStatusHistory::class)->latest();
    }

    public function isDelivery(): bool
    {
        return ($this->fulfillment ?? 'delivery') === 'delivery';
    }

    public function isFinal(): bool
    {
        return in_array($this->status, ['selesai', 'ditolak'], true);
    }

    public function recalculateTotal(): void
    {
        $this->total_amount = (int) $this->items()->sum('subtotal');
        $this->save();
    }
}
