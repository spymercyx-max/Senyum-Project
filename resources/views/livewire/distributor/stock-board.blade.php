<div class="sn-card">
    <p class="sn-kicker mb-2">Stok Distributorku</p>
    <input type="search" wire:model.live="q" class="sn-input sn-input-sm" placeholder="Cari produk..." aria-label="Cari produk">
    <p class="font-mono text-[11px] uppercase tracking-widest mt-2">Total unit tersedia: <strong>{{ $totalUnits }} pcs</strong></p>
    <div class="grid gap-2 mt-3">
        @forelse ($lines as $line)
            <div class="flex items-center gap-2 border-2 border-black rounded px-3 py-2 bg-[#FFF9EC] text-sm">
                <span class="flex-1"><strong>{{ $line['product']->name }}</strong><br><span class="text-xs text-neutral-600">Rp {{ number_format($line['product']->price, 0, ',', '.') }}</span></span>
                <span class="sn-badge {{ $line['qty'] <= 0 ? 'sn-badge--danger' : ($line['qty'] <= 10 ? 'sn-badge--pending' : 'sn-badge--success') }}">{{ $line['product']->name }} — {{ $line['qty'] }}</span>
            </div>
        @empty
            <p class="text-sm text-neutral-500">Belum ada stok. <a class="underline font-bold" href="{{ route('distributor.po') }}">Minta stok (PO) →</a></p>
        @endforelse
    </div>
</div>
