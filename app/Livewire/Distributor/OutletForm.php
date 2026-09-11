<?php

namespace App\Livewire\Distributor;

use App\Models\ActivityLog;
use App\Models\Outlet;
use Livewire\Component;
use Livewire\WithPagination;

class OutletForm extends Component
{
    use WithPagination;

    public string $q = '';

    public ?int $confirmId = null;

    public ?int $deleteId = null;

    public ?string $notice = null;

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function confirmToggle(int $id): void
    {
        $this->confirmId = $id;
        $this->deleteId = null;
        $this->notice = null;
    }

    public function cancelToggle(): void
    {
        $this->confirmId = null;
    }

    public function doToggle(): void
    {
        $user = auth()->user();
        $outlet = Outlet::find($this->confirmId);

        if (! $outlet || (int) $outlet->distributor_id !== (int) $user->id) {
            $this->addError('q', 'Outlet tidak ditemukan atau bukan milik Anda.');
            $this->confirmId = null;

            return;
        }

        $outlet->status = $outlet->status === 'active' ? 'inactive' : 'active';
        $outlet->save();

        ActivityLog::create([
            'actor_id' => $user->id,
            'action' => 'outlet.status_changed',
            'entity' => Outlet::class,
            'entity_id' => $outlet->id,
            'metadata' => ['name' => $outlet->name, 'status' => $outlet->status],
            'ip' => request()->ip(),
        ]);

        $this->notice = "Status {$outlet->name} sekarang ".($outlet->status === 'active' ? 'AKTIF' : 'NONAKTIF').'.';
        $this->confirmId = null;
    }

    public function render()
    {
        $user = auth()->user();
        $q = trim($this->q);

        $outlets = $user->outlets()->with(['images', 'territory'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('city', 'like', "%{$q}%")
                        ->orWhere('district', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(8);

        return view('livewire.distributor.outlet-form', [
            'outlets' => $outlets,
        ]);
    }
}
