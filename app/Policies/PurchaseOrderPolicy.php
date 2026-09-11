<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PurchaseOrder $order): bool
    {
        if ($user->isDeveloper()) {
            return true;
        }

        return (int) $order->distributor_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isDeveloper() || $user->isApprovedDistributor();
    }

    public function update(User $user, PurchaseOrder $order): bool
    {
        if ($user->isDeveloper()) {
            return true;
        }

        return (int) $order->distributor_id === (int) $user->id;
    }

    public function delete(User $user, PurchaseOrder $order): bool
    {
        return $user->isDeveloper();
    }
}
