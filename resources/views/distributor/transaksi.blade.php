@extends('layouts.distributor')

@section('title', 'Riwayat Transaksi — Field Workspace')

@section('content')
<p class="sn-kicker">Sales &middot; Transaksi</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Riwayat Penjualan</h1>
<p class="mt-1 text-sm text-neutral-600">Semua penjualan yang kamu catat ke outlet. Tanggal yang tampil adalah tanggal bisnis (sold_at). Untuk pesanan baru yang cepat, pakai <a class="underline font-bold" href="{{ route('distributor.pemesanan') }}">halaman pemesanan</a>.</p>

@if (session('success'))
    <div class="mt-4"><x-senyum-alert type="success" :message="session('success')" /></div>
@endif
@if ($errors->any())
    <div class="mt-4"><x-senyum-alert type="danger" title="Belum bisa disimpan"><ul class="list-disc ml-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-senyum-alert></div>
@endif

{{-- QUICK FORM --}}
<details class="sn-card mt-5">
    <summary class="font-display font-black uppercase cursor-pointer">+ Catat penjualan dari sini</summary>
    <p class="sn-help mb-3">Isi outlet dan produk yang terjual. Cocok untuk mencatat cepat tanpa pindah halaman.</p>
    <form method="POST" action="{{ route('distributor.transaksi.store') }}">
        @csrf
        <div class="grid gap-3">
            <div>
                <label class="sn-label" for="trx-outlet">Outlet yang membeli</label>
                <select id="trx-outlet" name="outlet_id" class="sn-select @error('outlet_id') sn-select--error @enderror" required>
                    <option value="">— Pilih outlet —</option>
                    @foreach ($outlets as $o)
                        <option value="{{ $o->id }}" @selected(old('outlet_id') == $o->id)>{{ $o->name }}</option>
                    @endforeach
                </select>
                @error('outlet_id')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="sn-label" for="trx-date">Tanggal penjualan</label>
                <input id="trx-date" type="date" name="sold_at" class="sn-input @error('sold_at') sn-input--error @enderror" value="{{ old('sold_at', today()->toDateString()) }}">
                <p class="sn-help">Tanggal bisnis transaksi (bukan waktu pencatatan).</p>
                @error('sold_at')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
            <div id="trx-rows" class="grid gap-2">
                <div class="border-2 border-black rounded-sm p-2 bg-white" data-row>
                    <div class="grid grid-cols-[1fr_70px_110px] gap-2" data-row-inputs>
                        <select name="items[0][product_id]" class="sn-select trx-product" required aria-label="Produk">
                            <option value="">— Pilih produk —</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->name }} — Rp {{ number_format($p->price, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="items[0][qty]" class="sn-input trx-qty" min="1" value="1" aria-label="Jumlah" required>
                        <input type="number" name="items[0][price]" class="sn-input trx-price" min="0" placeholder="Harga" aria-label="Harga per pcs" value="">
                    </div>
                    <p class="text-xs text-right mt-1">Subtotal: <strong data-row-subtotal>Rp 0</strong></p>
                </div>
            </div>
            <button type="button" id="trx-add" class="sn-btn sn-btn-ghost sn-btn-sm w-full md:w-auto">+ Tambah produk</button>
            <div class="flex items-center justify-between border-[3px] border-black rounded bg-[#FFD21F] px-4 py-3">
                <span class="font-mono text-xs font-bold uppercase tracking-widest">Total</span>
                <strong class="font-display text-xl" id="trx-total">Rp 0</strong>
            </div>
            <div>
                <label class="sn-label" for="trx-notes">Catatan (opsional)</label>
                <textarea id="trx-notes" name="notes" class="sn-textarea" rows="2" placeholder="Contoh: dibayar tunai, bayar tempo 3 hari...">{{ old('notes') }}</textarea>
                @error('notes')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="sn-btn sn-btn-yellow w-full md:w-auto">Simpan Penjualan</button>
        </div>
    </form>
</details>

{{-- HISTORY --}}
<div class="flex flex-wrap items-center gap-2 mt-7 mb-3">
    <h2 class="font-display font-black uppercase text-lg">Semua Transaksi</h2>
    <span class="ml-auto flex gap-2 text-sm">
        <a href="{{ route('distributor.transaksi') }}" class="sn-btn sn-btn-sm {{ ! $showVoid ? 'sn-btn-primary' : 'sn-btn-ghost' }}">Aktif</a>
        <a href="{{ route('distributor.transaksi', ['status' => 'void']) }}" class="sn-btn sn-btn-sm {{ $showVoid ? 'sn-btn-primary' : 'sn-btn-ghost' }}">Dibatalkan</a>
    </span>
</div>
@if ($showVoid)
    <p class="text-sm mb-3"><x-senyum-badge status="void">DIBATALKAN</x-senyum-badge> <span class="text-neutral-600">Menampilkan transaksi yang dibatalkan. Stok sudah dikembalikan.</span></p>
@endif
@if ($transactions->isEmpty())
    <x-senyum-empty-state title="Belum ada transaksi" message="Setiap penjualan ke outlet akan tercatat di sini." action="Buat Pesanan" :href="route('distributor.pemesanan')" />
@else
    <div class="sn-table-wrap hidden md:block">
        <table class="sn-table">
            <thead><tr><th>Kode</th><th>Outlet</th><th>Items (N produk)</th><th class="sn-num">Jumlah Barang (pcs)</th><th class="sn-num">Total</th><th>Tanggal bisnis</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach ($transactions as $t)
                    <tr>
                        <td class="font-mono font-bold">{{ $t->code }}</td>
                        <td>{{ $t->outlet?->name ?? '—' }}</td>
                        <td class="text-xs">{{ $t->items->count() }} produk: {{ $t->items->map(fn ($i) => ($i->product?->name ?? $i->product_name ?? '?') . ' ×' . $i->qty)->join(', ') }}</td>
                        <td class="sn-num">{{ $t->items->sum('qty') }} pcs</td>
                        <td class="sn-num">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td>
                        <td>{{ $t->sold_at?->format('d M Y') ?? $t->created_at->format('d M Y') }}</td>
                        <td><x-senyum-badge :status="$t->status ?? 'completed'">{{ ($t->status ?? '') === 'void' ? 'DIBATALKAN' : ($t->status ?? 'completed') }}</x-senyum-badge></td>
                        <td><a href="{{ route('distributor.transaksi.show', $t->id) }}" class="underline font-bold text-sm">DETAIL</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="grid gap-2 md:hidden">
        @foreach ($transactions as $t)
            <div class="sn-card !p-3 text-sm">
                <div class="flex items-center gap-2">
                    <strong class="font-mono">{{ $t->code }}</strong>
                    <x-senyum-badge :status="$t->status ?? 'completed'">{{ ($t->status ?? '') === 'void' ? 'DIBATALKAN' : ($t->status ?? 'completed') }}</x-senyum-badge>
                </div>
                <span class="block text-neutral-600 mt-1">{{ $t->outlet?->name ?? '—' }} &middot; {{ $t->sold_at?->format('d M Y') ?? $t->created_at->format('d M Y') }}</span>
                <span class="block text-xs text-neutral-500">{{ $t->items->count() }} produk: {{ $t->items->map(fn ($i) => ($i->product?->name ?? $i->product_name ?? '?') . ' ×' . $i->qty)->join(', ') }}</span>
                <span class="block text-xs mt-1">Jumlah barang: <strong>{{ $t->items->sum('qty') }} pcs</strong></span>
                <div class="flex items-center justify-between mt-1">
                    <strong>Rp {{ number_format($t->total_amount, 0, ',', '.') }}</strong>
                    <a href="{{ route('distributor.transaksi.show', $t->id) }}" class="underline font-bold">DETAIL</a>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-3">{{ $transactions->links() }}</div>
@endif

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">Rekap penjualanmu di <a class="underline font-bold" href="{{ route('distributor.pembukuan') }}">pembukuan</a> setiap akhir hari.</x-senyum-alert>
</div>

@push('scripts')
<script>
(function () {
    var wrap = document.getElementById('trx-rows');
    var add = document.getElementById('trx-add');
    var totalEl = document.getElementById('trx-total');
    if (!wrap || !add) return;
    var idx = 1;
    function fmt(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }
    function rowSubtotal(row) {
        var prod = row.querySelector('.trx-product');
        var qty = row.querySelector('.trx-qty');
        var price = row.querySelector('.trx-price');
        var base = 0;
        if (prod && prod.selectedOptions.length) {
            base = parseInt(prod.selectedOptions[0].getAttribute('data-price') || '0', 10) || 0;
        }
        var p = price && price.value !== '' ? (parseInt(price.value, 10) || 0) : base;
        var q = qty ? (parseInt(qty.value, 10) || 0) : 0;
        return p * q;
    }
    function recalc() {
        var total = 0;
        wrap.querySelectorAll('[data-row]').forEach(function (row) {
            var sub = rowSubtotal(row);
            total += sub;
            var label = row.querySelector('[data-row-subtotal]');
            if (label) label.textContent = fmt(sub);
        });
        if (totalEl) totalEl.textContent = fmt(total);
    }
    wrap.addEventListener('input', recalc);
    wrap.addEventListener('change', recalc);
    add.addEventListener('click', function () {
        var first = wrap.querySelector('[data-row]');
        var clone = first.cloneNode(true);
        clone.querySelectorAll('select, input').forEach(function (el) {
            el.name = el.name.replace(/items\[\d+\]/, 'items[' + idx + ']');
            if (el.classList.contains('trx-qty')) el.value = 1;
            else if (el.classList.contains('trx-price')) el.value = '';
            else if (el.tagName === 'SELECT') el.selectedIndex = 0;
        });
        var label = clone.querySelector('[data-row-subtotal]');
        if (label) label.textContent = 'Rp 0';
        wrap.appendChild(clone);
        idx++;
        recalc();
    });
    recalc();
})();
</script>
@endpush
@endsection
