<?php

namespace App\Livewire\Developer;

use App\Models\User;
use App\Services\DistributorApprovalService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Component;

class DistributorApproval extends Component
{
    public int $userId;

    public ?string $confirming = null;

    public ?string $reason = null;

    public ?string $notice = null;

    public ?string $error = null;

    public function mount(int $userId): void
    {
        $this->userId = $userId;
    }

    public function confirm(string $action): void
    {
        $this->confirming = $action;
        $this->error = null;
        $this->reason = null;
    }

    public function cancel(): void
    {
        $this->confirming = null;
        $this->reason = null;
    }

    public function approve(DistributorApprovalService $service): void
    {
        $user = $this->distributor();

        try {
            $service->approve(auth()->user(), $user);
        } catch (ModelNotFoundException) {
            $this->error = 'Profil distributor tidak ditemukan sehingga tidak bisa disetujui.';

            return;
        } catch (\Throwable) {
            $this->error = 'Gagal menyetujui distributor. Silakan coba lagi.';

            return;
        }

        $this->confirming = null;
        $this->notice = "Distributor {$user->name} berhasil disetujui.";
    }

    public function reject(DistributorApprovalService $service): void
    {
        $this->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $user = $this->distributor();

        try {
            $service->reject(auth()->user(), $user, $this->reason);
        } catch (ModelNotFoundException) {
            $this->error = 'Profil distributor tidak ditemukan sehingga tidak bisa ditolak.';

            return;
        } catch (\Throwable) {
            $this->error = 'Gagal menolak distributor. Silakan coba lagi.';

            return;
        }

        $this->confirming = null;
        $this->reason = null;
        $this->notice = "Pengajuan distributor {$user->name} berhasil ditolak.";
    }

    public function suspend(DistributorApprovalService $service): void
    {
        $this->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $user = $this->distributor();

        try {
            $service->suspend(auth()->user(), $user, $this->reason);
        } catch (ModelNotFoundException) {
            $this->error = 'Profil distributor tidak ditemukan sehingga tidak bisa ditangguhkan.';

            return;
        } catch (\Throwable) {
            $this->error = 'Gagal menangguhkan distributor. Silakan coba lagi.';

            return;
        }

        $this->confirming = null;
        $this->reason = null;
        $this->notice = "Distributor {$user->name} berhasil ditangguhkan.";
    }

    private function distributor(): User
    {
        return User::with(['profile', 'distributorProfile.territory'])->findOrFail($this->userId);
    }

    public function render()
    {
        return view('livewire.developer.distributor-approval', [
            'user' => $this->distributor(),
        ]);
    }
}
