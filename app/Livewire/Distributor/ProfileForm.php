<?php

namespace App\Livewire\Distributor;

use Livewire\Component;

class ProfileForm extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $whatsapp = '';

    public string $experience = '';

    public string $notes = '';

    public ?string $success = null;

    public function mount(): void
    {
        $user = auth()->user()->loadMissing('profile');

        $this->name = (string) ($user->name ?? '');
        $this->phone = (string) ($user->phone ?? '');
        $this->whatsapp = (string) ($user->profile?->whatsapp ?? '');
        $this->experience = (string) ($user->profile?->experience ?? '');
        $this->notes = (string) ($user->profile?->notes ?? '');
    }

    public function save(): void
    {
        $this->success = null;

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'experience' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ], [], [
            'name' => 'nama',
            'phone' => 'telepon',
            'whatsapp' => 'whatsapp',
        ]);

        $user = auth()->user();
        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?: null,
        ]);

        $user->profile()->updateOrCreate(['user_id' => $user->id], [
            'whatsapp' => $validated['whatsapp'] ?: null,
            'experience' => $validated['experience'] ?: null,
            'notes' => $validated['notes'] ?: null,
        ]);

        $this->success = 'Profil berhasil disimpan.';
    }

    public function render()
    {
        return view('livewire.distributor.profile-form');
    }
}
