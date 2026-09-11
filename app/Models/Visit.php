<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'outlet_id',
        'visited_at',
        'status',
        'notes',
        'follow_up_at',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
            'follow_up_at' => 'datetime',
            'status' => 'string',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
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
}
