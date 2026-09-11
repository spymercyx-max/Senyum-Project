<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'territory_id',
        'distributor_id',
        'name',
        'address',
        'city',
        'district',
        'phone',
        'latitude',
        'longitude',
        'google_maps_url',
        'location_source',
        'location_verified_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'location_verified_at' => 'datetime',
            'status' => 'string',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(OutletImage::class)->orderBy('sort_order');
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null
            && ((float) $this->latitude !== 0.0 || (float) $this->longitude !== 0.0);
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Territory::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(OutletContact::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function consignments(): HasMany
    {
        return $this->hasMany(Consignment::class);
    }
}
