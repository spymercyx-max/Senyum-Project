<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderInquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'whatsapp',
        'message',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
