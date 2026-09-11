<?php

namespace App\Policies;

use App\Models\Outlet;
use App\Models\User;

class OutletPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Outlet $outlet): bool
    {
        if ($user->isDeveloper()) {
            return true;
        }

        if ((int) $outlet->distributor_id === (int) $user->id) {
            return true;
        }

        $territoryId = $user->distributorProfile?->territory_id;

        return $territoryId !== null && (int) $outlet->territory_id === (int) $territoryId;
    }

    public function create(User $user): bool
    {
        return $user->isDeveloper() || $user->isApprovedDistributor();
    }

    public function update(User $user, Outlet $outlet): bool
    {
        if ($user->isDeveloper()) {
            return true;
        }

        return (int) $outlet->distributor_id === (int) $user->id;
    }

    public function delete(User $user, Outlet $outlet): bool
    {
        if ($user->isDeveloper()) {
            return true;
        }

        return (int) $outlet->distributor_id === (int) $user->id;
    }
}
