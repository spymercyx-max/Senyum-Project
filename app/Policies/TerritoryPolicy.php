<?php

namespace App\Policies;

use App\Models\Territory;
use App\Models\User;

class TerritoryPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Territory $territory): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isDeveloper();
    }

    public function update(User $user, Territory $territory): bool
    {
        return $user->isDeveloper();
    }

    public function delete(User $user, Territory $territory): bool
    {
        return $user->isDeveloper();
    }
}
