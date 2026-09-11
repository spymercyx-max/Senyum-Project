<div>
    @if ($notice)
        <x-senyum-alert type="success" :message="$notice" class="mb-4" />
    @endif

    <form wire:submit="save" class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="sn-label" for="pf-name">Nama</label>
            <input id="pf-name" type="text" wire:model="name" class="sn-input" maxlength="255" required>
            @error('name') <p class="sn-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="sn-label" for="pf-phone">Telepon</label>
            <input id="pf-phone" type="text" wire:model="phone" class="sn-input" maxlength="30" placeholder="08…">
            @error('phone') <p class="sn-error">{{ $message }}</p> @enderror
        </div>
        <div class="sm:col-span-2">
            <button type="submit" class="sn-btn sn-btn-primary sn-btn-sm">Simpan profil</button>
        </div>
    </form>
</div>
