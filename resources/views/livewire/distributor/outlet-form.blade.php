<div>
    <div class="flex gap-2">
        <input type="search" wire:model.live="q" class="sn-input" placeholder="Cari nama outlet / kota / kecamatan..." aria-label="Cari outlet">
        <a href="{{ route('distributor.outlet.create') }}" class="sn-btn sn-btn-yellow sn-btn-sm whitespace-nowrap">+ Baru</a>
    </div>
    @error('q')<p class="sn-error mt-1">{{ $message }}</p>@enderror
    @if ($notice)
        <div class="mt-3"><x-senyum-alert type="success" :message="$notice" /></div>
    @endif

    @php
        $sq = trim($q ?? '');
        $countBase = auth()->user()->outlets()
            ->when($sq !== '', function ($query) use ($sq) {
                $query->where(function ($w) use ($sq) {
                    $w->where('name', 'like', "%{$sq}%")
                        ->orWhere('city', 'like', "%{$sq}%")
                        ->orWhere('district', 'like', "%{$sq}%");
                });
            });
        $totalCount = (clone $countBase)->count();
        $activeCount = (clone $countBase)->where('status', 'active')->count();
        $inactiveCount = $totalCount - $activeCount;
    @endphp
    <div class="mt-3 flex flex-wrap items-center gap-2 text-sm border-2 border-black rounded-sm bg-white px-3 py-2">
        <span>Total: <strong>{{ $totalCount }}</strong></span>
        <span aria-hidden="true">·</span>
        <span>Aktif: <strong>{{ $activeCount }}</strong></span>
        <span aria-hidden="true">·</span>
        <span>Nonaktif: <strong>{{ $inactiveCount }}</strong></span>
        <span class="ml-auto flex gap-2">
            <a href="{{ route('distributor.outlet') }}" class="underline font-bold">Semua outlet</a>
            <a href="{{ route('distributor.outlet.create') }}" class="underline font-bold">+ Tambah</a>
        </span>
    </div>

    @if ($outlets->isEmpty())
        <div class="mt-4">
            @if (trim($q) !== '')
                <x-senyum-empty-state title="Tidak ketemu" :message="'Tidak ada outlet cocok dengan \'' . $q . '\'.'" />
            @else
                <x-senyum-empty-state title="Belum ada outlet" message="Tambahkan warung/toko pertamamu sekarang." action="Tambah Outlet" :href="route('distributor.outlet.create')" />
            @endif
        </div>
    @else
        <div class="grid gap-2 mt-4 sm:grid-cols-2">
            @foreach ($outlets as $o)
                <div class="sn-card !p-4" wire:key="outlet-{{ $o->id }}">
                    <div class="flex items-start gap-3">
                        @if ($o->images->isNotEmpty())
                            <img src="{{ $o->images->first()->url() }}" alt="Foto {{ $o->name }}" class="w-14 h-14 object-cover border-2 border-black rounded-sm shrink-0" loading="lazy">
                        @else
                            <div class="w-14 h-14 border-2 border-black rounded-sm bg-[#0BBCD6] flex items-center justify-center font-black text-black shrink-0" aria-hidden="true">{{ strtoupper(substr($o->name, 0, 1)) }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('distributor.outlet.show', $o) }}" class="font-bold hover:underline">{{ $o->name }}</a>
                                <x-senyum-badge :status="$o->status">{{ $o->status === 'active' ? 'Aktif' : 'Nonaktif' }}</x-senyum-badge>
                            </div>
                            <p class="text-sm text-neutral-600 mt-0.5">{{ $o->city }} &middot; Kec. {{ $o->district }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <a href="{{ route('distributor.outlet.show', $o) }}" class="sn-btn sn-btn-ghost sn-btn-sm">Detail</a>
                        <a href="{{ route('distributor.outlet.edit', $o) }}" class="sn-btn sn-btn-ghost sn-btn-sm">Edit</a>
                        <button type="button" wire:click="confirmToggle({{ $o->id }})" class="sn-btn sn-btn-sm {{ $o->status === 'active' ? 'sn-btn-danger' : 'sn-btn-cyan' }}">
                            {{ $o->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                        <button type="button" data-modal-open="#del-outlet-{{ $o->id }}" class="sn-btn sn-btn-ghost sn-btn-sm !text-red-700">Hapus</button>
                    </div>
                    <div id="del-outlet-{{ $o->id }}" class="sn-modal-backdrop" role="dialog" aria-modal="true" aria-label="Konfirmasi hapus {{ $o->name }}">
                        <div class="sn-modal">
                            <h3 class="sn-modal-title">Hapus outlet?</h3>
                            <p class="text-sm leading-relaxed"><strong>{{ $o->name }}</strong> akan dihapus (arsip, tidak hilang permanen). Lanjutkan?</p>
                            <div class="flex gap-2 justify-end mt-6">
                                <button type="button" data-modal-close class="sn-btn sn-btn-ghost sn-btn-sm">Batal</button>
                                <form method="POST" action="{{ route('distributor.outlet.destroy', $o) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="sn-btn sn-btn-danger sn-btn-sm">Ya, hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-3">{{ $outlets->links() }}</div>
    @endif

    @if ($confirmId)
        <div class="sn-modal-backdrop is-open" role="dialog" aria-modal="true" aria-label="Konfirmasi ubah status">
            <div class="sn-modal">
                <h3 class="sn-modal-title">Ubah status outlet?</h3>
                <p class="text-sm leading-relaxed">Outlet yang dinonaktifkan tidak bisa dipilih untuk jual/visit baru, tapi datanya tetap tersimpan. Bisa diaktifkan lagi kapan saja.</p>
                <div class="flex gap-2 justify-end mt-6">
                    <button type="button" wire:click="cancelToggle" class="sn-btn sn-btn-ghost sn-btn-sm">Batal</button>
                    <button type="button" wire:click="doToggle" class="sn-btn sn-btn-danger sn-btn-sm">Ya, ubah</button>
                </div>
            </div>
        </div>
    @endif
</div>
