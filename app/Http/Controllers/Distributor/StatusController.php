<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StatusController extends Controller
{
    public function pending(WhatsAppService $wa): View|RedirectResponse
    {
        if ($this->shouldLeaveStatusPage()) {
            return $this->leaveStatusPage();
        }

        return view('distributor.status-pending', [
            'waUrl' => $wa->waUrl(
                $wa->developerNumber(),
                'Halo SENYUM! Saya ingin konsultasi status pendaftaran distributor saya. Terima kasih.'
            ),
        ]);
    }

    public function rejected(WhatsAppService $wa): View|RedirectResponse
    {
        if ($this->shouldLeaveStatusPage()) {
            return $this->leaveStatusPage();
        }

        return view('distributor.status-rejected', [
            'waUrl' => $wa->waUrl(
                $wa->developerNumber(),
                'Halo SENYUM! Pengajuan distributor saya ditolak. Saya ingin tahu cara memperbaikinya. Terima kasih.'
            ),
        ]);
    }

    public function suspended(WhatsAppService $wa): View|RedirectResponse
    {
        if ($this->shouldLeaveStatusPage()) {
            return $this->leaveStatusPage();
        }

        return view('distributor.status-suspended', [
            'waUrl' => $wa->waUrl(
                $wa->developerNumber(),
                'Halo SENYUM! Akun distributor saya ditangguhkan. Saya ingin mengajukan banding. Terima kasih.'
            ),
        ]);
    }

    protected function shouldLeaveStatusPage(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isDeveloper()) {
            return true;
        }

        return $user->isDistributor() && $user->distributorStatus() === 'approved';
    }

    protected function leaveStatusPage(): RedirectResponse
    {
        $user = auth()->user();

        if ($user && $user->isDeveloper()) {
            return redirect()->route('developer.dashboard');
        }

        return redirect()->route('distributor.dashboard');
    }
}
