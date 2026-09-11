<div class="sn-card">
    <p class="sn-kicker mb-2">Ubah Profil</p>
    @if ($success)
        <div class="mb-4"><x-senyum-alert type="success" :message="$success" /></div>
    @endif

    <form wire:submit="save">
        <div class="grid gap-4">
            <div>
                <label class="sn-label" for="pf-name">Nama lengkap</label>
                <input id="pf-name" type="text" wire:model="name" class="sn-input @error('name') sn-input--error @enderror">
                @error('name')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="sn-label" for="pf-phone">No. telepon</label>
                    <input id="pf-phone" type="text" wire:model="phone" class="sn-input @error('phone') sn-input--error @enderror" placeholder="08...">
                    @error('phone')<p class="sn-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="sn-label" for="pf-wa">WhatsApp aktif</label>
                    <input id="pf-wa" type="text" wire:model="whatsapp" class="sn-input @error('whatsapp') sn-input--error @enderror" placeholder="08...">
                    <p class="sn-help">Untuk dihubungi developer.</p>
                    @error('whatsapp')<p class="sn-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="sn-label" for="pf-exp">Pengalaman usaha</label>
                <textarea id="pf-exp" wire:model="experience" class="sn-textarea" rows="2" placeholder="Contoh: 3 tahun jualan rokok eceran di pasar"></textarea>
                @error('experience')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="sn-label" for="pf-notes">Catatan (opsional)</label>
                <textarea id="pf-notes" wire:model="notes" class="sn-textarea" rows="2" placeholder="Hal lain yang perlu developer tahu"></textarea>
                @error('notes')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="sn-btn sn-btn-primary w-full md:w-auto" wire:loading.attr="disabled">
                <span wire:loading.remove>Simpan Profil</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
