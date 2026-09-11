<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorStock extends Model
{
    protected $fillable = [
        'distributor_id',
        'product_id',
        'qty',
    ];

    protected function casts(): array
    {
        return ['qty' => 'integer'];
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
