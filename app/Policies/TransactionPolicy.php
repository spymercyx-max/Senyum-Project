<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->isDeveloper()) {
            return true;
        }

        return (int) $transaction->distributor_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isDeveloper() || $user->isApprovedDistributor();
    }

    public function update(User $user, Transaction $transaction): bool
    {
        if ($user->isDeveloper()) {
            return true;
        }

        return (int) $transaction->distributor_id === (int) $user->id;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->isDeveloper();
    }
}
