<?php

namespace App\Livewire\Distributor;

use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\Visit;
use Livewire\Component;

class VisitForm extends Component
{
    public string $outlet_id = '';

    public string $visited_at = '';

    public string $status = 'done';

    public string $notes = '';

    public string $follow_up_at = '';

    public ?string $success = null;

    public function mount(): void
    {
        $this->visited_at = today()->toDateString();
    }

    public function submit(): void
    {
        $this->success = null;

        $validated = $this->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'visited_at' => ['required', 'date'],
            'status' => ['required', 'in:done,planned,cancelled'],
            'notes' => ['nullable', 'string'],
            'follow_up_at' => ['nullable', 'date', 'after:visited_at'],
        ], [], [
            'outlet_id' => 'outlet',
            'visited_at' => 'tanggal kunjungan',
            'status' => 'status',
            'follow_up_at' => 'tindak lanjut',
        ]);

        $user = auth()->user();
        $outlet = Outlet::findOrFail($validated['outlet_id']);

        if ((int) $outlet->distributor_id !== (int) $user->id) {
            $this->addError('outlet_id', 'Outlet ini bukan milik Anda.');

            return;
        }

        $visit = Visit::create(array_merge($validated, [
            'distributor_id' => $user->id,
            'follow_up_at' => $validated['follow_up_at'] ?: null,
        ]));

        ActivityLog::create([
            'actor_id' => $user->id,
            'action' => 'visit.created',
            'entity' => Visit::class,
            'entity_id' => $visit->id,
            'metadata' => ['outlet' => $outlet->name],
            'ip' => request()->ip(),
        ]);

        $this->success = "Kunjungan ke {$outlet->name} berhasil dicatat.";
        $this->reset(['outlet_id', 'notes', 'follow_up_at']);
        $this->status = 'done';
        $this->visited_at = today()->toDateString();
    }

    public function render()
    {
        $user = auth()->user();
        $outlets = $user ? $user->outlets()->where('status', 'active')->orderBy('name')->get() : collect();
        $recent = $user ? $user->visits()->with('outlet')->latest('visited_at')->limit(5)->get() : collect();

        return view('livewire.distributor.visit-form', [
            'outlets' => $outlets,
            'recent' => $recent,
        ]);
    }
}
