<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Territory;
use App\Models\User;

class DistributorApprovalService
{
    protected function log(User $dev, User $dist, string $action, array $extra = []): void
    {
        ActivityLog::create([
            'actor_id' => $dev->id,
            'action' => $action,
            'entity' => User::class,
            'entity_id' => $dist->id,
            'metadata' => array_merge(['username' => $dist->username], $extra),
            'ip' => request()->ip(),
        ]);
    }

    protected function notify(User $dist, string $title, ?string $body = null, array $data = []): void
    {
        Notification::create([
            'user_id' => $dist->id,
            'type' => 'distributor_status',
            'title' => $title,
            'body' => $body,
            'data' => $data ?: null,
        ]);
    }

    public function approve(User $dev, User $dist): User
    {
        $profile = $dist->distributorProfile()->firstOrFail();
        $profile->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $dev->id,
        ]);

        $this->log($dev, $dist, 'distributor.approved');
        $this->notify($dist, 'Akun distributor disetujui', 'Selamat! Akun distributor Anda telah disetujui.');

        return $dist->refresh()->load('distributorProfile');
    }

    public function reject(User $dev, User $dist, ?string $reason = null): User
    {
        $profile = $dist->distributorProfile()->firstOrFail();
        $profile->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => $dev->id,
        ]);

        $this->log($dev, $dist, 'distributor.rejected', ['reason' => $reason]);
        $this->notify($dist, 'Akun distributor ditolak', $reason ?? 'Mohon maaf, pengajuan distributor Anda ditolak.');

        return $dist->refresh()->load('distributorProfile');
    }

    public function suspend(User $dev, User $dist, ?string $reason = null): User
    {
        $profile = $dist->distributorProfile()->firstOrFail();
        $profile->update(['status' => 'suspended']);

        $this->log($dev, $dist, 'distributor.suspended', ['reason' => $reason]);
        $this->notify($dist, 'Akun distributor ditangguhkan', $reason ?? 'Akun distributor Anda ditangguhkan sementara.');

        return $dist->refresh()->load('distributorProfile');
    }

    public function assignTerritory(User $dev, User $dist, Territory $territory): User
    {
        $profile = $dist->distributorProfile()->firstOrFail();
        $profile->update(['territory_id' => $territory->id]);

        $this->log($dev, $dist, 'distributor.territory_assigned', ['territory_id' => $territory->id]);
        $this->notify(
            $dist,
            'Wilayah distribusi diperbarui',
            'Wilayah Anda: ' . $territory->city . ' – ' . $territory->district,
            ['territory_id' => $territory->id]
        );

        return $dist->refresh()->load('distributorProfile');
    }
}
