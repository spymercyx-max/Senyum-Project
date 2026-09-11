<?php

namespace App\Policies;

use App\Models\User;

class DistributorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isDeveloper();
    }

    public function view(User $user, User $distributor): bool
    {
        if ($user->isDeveloper()) {
            return true;
        }

        return $user->id === $distributor->id;
    }

    public function create(User $user): bool
    {
        return $user->isDeveloper();
    }

    public function update(User $user, User $distributor): bool
    {
        if ($user->isDeveloper()) {
            return true;
        }

        return $user->id === $distributor->id;
    }

    public function delete(User $user, User $distributor): bool
    {
        return $user->isDeveloper();
    }
}
