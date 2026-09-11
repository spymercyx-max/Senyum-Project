<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutletContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'name',
        'phone',
        'role',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }
}
