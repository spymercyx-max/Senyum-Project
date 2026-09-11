<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'role' => 'string',
            'status' => 'string',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function distributorProfile(): HasOne
    {
        return $this->hasOne(DistributorProfile::class);
    }

    public function territory(): HasOneThrough
    {
        return $this->hasOneThrough(
            Territory::class,
            DistributorProfile::class,
            'user_id',
            'id',
            'id',
            'territory_id'
        );
    }

    public function distributorTerritory(): ?Territory
    {
        return $this->distributorProfile?->territory;
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class, 'distributor_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'distributor_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'distributor_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'distributor_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'actor_id');
    }

    public function getTerritoryAttribute(): ?Territory
    {
        return $this->distributorTerritory();
    }

    public function isDeveloper(): bool
    {
        return $this->role === 'developer';
    }

    public function isDistributor(): bool
    {
        return $this->role === 'distributor';
    }

    public function distributorStatus(): ?string
    {
        return $this->distributorProfile?->status;
    }

    public function isApprovedDistributor(): bool
    {
        return $this->isDistributor() && $this->distributorStatus() === 'approved';
    }
}
