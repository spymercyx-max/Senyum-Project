<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'territory_id',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
