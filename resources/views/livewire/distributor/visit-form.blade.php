<div>
    <div class="sn-card">
        @if ($success)
            <div class="mb-4"><x-senyum-alert type="success" :message="$success" /></div>
        @endif

        <form wire:submit="submit">
            <div class="grid gap-4">
                <div>
                    <label class="sn-label" for="vf-outlet">Outlet yang dikunjungi</label>
                    <select id="vf-outlet" wire:model="outlet_id" class="sn-select @error('outlet_id') sn-select--error @enderror">
                        <option value="">— Pilih outlet —</option>
                        @foreach ($outlets as $o)
                            <option value="{{ $o->id }}">{{ $o->name }}</option>
                        @endforeach
                    </select>
                    @error('outlet_id')<p class="sn-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="sn-label" for="vf-date">Tanggal kunjung</label>
                        <input id="vf-date" type="date" wire:model="visited_at" class="sn-input @error('visited_at') sn-input--error @enderror">
                        @error('visited_at')<p class="sn-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="sn-label" for="vf-status">Hasil</label>
                        <select id="vf-status" wire:model="status" class="sn-select">
                            <option value="done">Sudah dikunjungi</option>
                            <option value="planned">Rencana (jadwal)</option>
                            <option value="cancelled">Batal</option>
                        </select>
                        @error('status')<p class="sn-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="sn-label" for="vf-notes">Catatan kunjungan</label>
                    <textarea id="vf-notes" wire:model="notes" class="sn-textarea" rows="2" placeholder="Contoh: stok tinggal 5, pemilik minta tambah varian..."></textarea>
                    <p class="sn-help">Tulis apa adanya — jadi pengingat saat datang lagi.</p>
                    @error('notes')<p class="sn-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="sn-label" for="vf-follow">Perlu datang lagi tanggal (opsional)</label>
                    <input id="vf-follow" type="date" wire:model="follow_up_at" class="sn-input @error('follow_up_at') sn-input--error @enderror">
                    <p class="sn-help">Akan muncul sebagai pengingat di dashboard.</p>
                    @error('follow_up_at')<p class="sn-error">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="sn-btn sn-btn-yellow w-full md:w-auto" wire:loading.attr="disabled">
                    <span wire:loading.remove>Simpan Kunjungan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>

    <h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Terakhir Dicatat</h2>
    @if ($recent->isEmpty())
        <x-senyum-empty-state title="Belum ada kunjungan" message="Kunjungan yang kamu catat akan muncul di sini." />
    @else
        <div class="grid gap-2">
            @foreach ($recent as $v)
                <div class="sn-card !p-3 text-sm">
                    <strong>{{ $v->outlet?->name ?? '—' }}</strong>
                    <x-senyum-badge :status="$v->status">{{ $v->status }}</x-senyum-badge>
                    <span class="block text-neutral-600 mt-1">{{ $v->visited_at?->format('d M Y') }}{{ $v->notes ? ' — ' . \Str::limit($v->notes, 80) : '' }}</span>
                    @if ($v->follow_up_at)<span class="block text-xs mt-1">Datang lagi: {{ $v->follow_up_at->format('d M Y') }}</span>@endif
                </div>
            @endforeach
        </div>
        <a href="{{ route('distributor.visit') }}" class="sn-btn sn-btn-ghost sn-btn-sm mt-3">Lihat semua riwayat</a>
    @endif
</div>
