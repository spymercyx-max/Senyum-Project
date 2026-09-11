<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Territory extends Model
{
    use HasFactory;

    protected $fillable = [
        'city',
        'district',
        'city_normalized',
        'district_normalized',
        'code',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function distributorProfiles(): HasMany
    {
        return $this->hasMany(DistributorProfile::class);
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function displayName(): string
    {
        return $this->city . ' – ' . $this->district;
    }
}
