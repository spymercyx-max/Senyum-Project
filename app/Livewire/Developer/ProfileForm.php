<?php

namespace App\Livewire\Developer;

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Component;

class ProfileForm extends Component
{
    public string $name = '';

    public ?string $phone = null;

    public ?string $notice = null;

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $this->name = (string) $user->name;
        $this->phone = $user->phone;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        /** @var User $user */
        $user = auth()->user();
        $user->update($data);

        ActivityLog::create([
            'actor_id' => $user->id,
            'action' => 'profile.updated',
            'entity' => User::class,
            'entity_id' => $user->id,
            'metadata' => ['name' => $user->name],
            'ip' => request()->ip(),
        ]);

        $this->notice = 'Profil berhasil diperbarui.';
    }

    public function render()
    {
        return view('livewire.developer.profile-form');
    }
}
