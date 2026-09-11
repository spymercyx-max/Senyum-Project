<?php

namespace App\Livewire\Developer;

use App\Models\Territory;
use App\Models\User;
use App\Services\DistributorApprovalService;
use Livewire\Component;

class TerritoryBoard extends Component
{
    public string $search = '';

    public ?int $territoryId = null;

    public ?int $assignTerritoryId = null;

    public ?int $distributorId = null;

    public ?string $notice = null;

    public ?string $error = null;

    public function mount(?int $territoryId = null): void
    {
        $this->territoryId = $territoryId;
        $this->assignTerritoryId = $territoryId;
    }

    public function assign(DistributorApprovalService $service): void
    {
        $this->validate([
            'assignTerritoryId' => ['required', 'exists:territories,id'],
            'distributorId' => ['required', 'exists:users,id'],
        ]);

        $territory = Territory::find($this->assignTerritoryId);
        $distributor = User::with('distributorProfile')->find($this->distributorId);

        if (! $territory || ! $distributor || $distributor->role !== 'distributor') {
            $this->error = 'Wilayah atau distributor tidak valid.';

            return;
        }

        if (($distributor->distributorProfile?->status) !== 'approved') {
            $this->error = 'Hanya distributor yang sudah disetujui yang bisa ditempatkan di wilayah.';

            return;
        }

        try {
            $service->assignTerritory(auth()->user(), $distributor, $territory);
        } catch (\Throwable) {
            $this->error = 'Gagal menempatkan distributor ke wilayah. Silakan coba lagi.';

            return;
        }

        $this->error = null;
        $this->notice = "Distributor {$distributor->name} berhasil ditempatkan di {$territory->city} – {$territory->district}.";
        $this->distributorId = null;
    }

    public function render()
    {
        if ($this->territoryId) {
            $territories = Territory::withCount(['distributorProfiles', 'outlets'])
                ->where('id', $this->territoryId)
                ->get();
        } else {
            $territories = Territory::withCount(['distributorProfiles', 'outlets'])
                ->when($this->search !== '', fn ($q) => $q->where(fn ($w) => $w
                    ->where('city', 'like', "%{$this->search}%")
                    ->orWhere('district', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%")))
                ->orderBy('city')
                ->orderBy('district')
                ->take(20)
                ->get();
        }

        $distributors = User::where('role', 'distributor')
            ->whereHas('distributorProfile', fn ($q) => $q->where('status', 'approved'))
            ->with(['distributorProfile.territory'])
            ->orderBy('name')
            ->get();

        $allTerritories = $this->territoryId
            ? $territories
            : Territory::orderBy('city')->orderBy('district')->get(['id', 'city', 'district']);

        return view('livewire.developer.territory-board', compact('territories', 'distributors', 'allTerritories'));
    }
}
