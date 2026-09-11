@extends('layouts.distributor')

@section('title', 'Minta Stok (PO) — Field Workspace')

@section('content')
<p class="sn-kicker">Operations &middot; Purchase Order</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Minta Stok</h1>
<p class="mt-1 text-sm text-neutral-600">Pesan stok ke developer. Pilih DIKIRIM (diantar + wajib bukti bayar) atau PICK-UP (ambil sendiri).</p>

@if (session('success'))
    <div class="mt-4"><x-senyum-alert type="success" :message="session('success')" /></div>
@endif
@if ($errors->any())
    <div class="mt-4"><x-senyum-alert type="danger" title="Belum bisa dikirim"><ul class="list-disc ml-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-senyum-alert></div>
@endif

<details class="sn-card mt-5" open>
    <summary class="font-display font-black uppercase cursor-pointer">+ Buat permintaan stok</summary>
    <form method="POST" action="{{ route('distributor.po.store') }}" enctype="multipart/form-data" class="mt-3" id="po-form">
        @csrf
        <div class="grid gap-3">
            <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="Mode pemenuhan">
                <label class="border-[3px] border-black rounded-sm px-3 py-2 text-center font-black text-sm cursor-pointer has-checked:bg-black has-checked:text-white">
                    <input type="radio" name="fulfillment" value="delivery" class="sr-only" @checked(old('fulfillment', $fulfillmentDefault ?? 'delivery') === 'delivery')>
                    DIKIRIM
                </label>
                <label class="border-[3px] border-black rounded-sm px-3 py-2 text-center font-black text-sm cursor-pointer has-checked:bg-black has-checked:text-white">
                    <input type="radio" name="fulfillment" value="pickup" class="sr-only" @checked(old('fulfillment', $fulfillmentDefault ?? 'delivery') === 'pickup')>
                    PICK-UP
                </label>
            </div>
            @error('fulfillment')<p class="sn-error">{{ $message }}</p>@enderror
            <div>
                <label class="sn-label" for="po-date">Tanggal pemesanan</label>
                <input id="po-date" type="date" name="order_date" class="sn-input @error('order_date') sn-input--error @enderror" value="{{ old('order_date', $today ?? today()->toDateString()) }}" required>
                <p class="sn-help">Tanggal bisnis bebas: hari ini, kemarin, atau mendatang.</p>
                @error('order_date')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
            <div id="po-rows" class="grid gap-2">
                <div class="grid grid-cols-[1fr_80px_auto] gap-2 items-start" data-row>
                    <select name="items[0][product_id]" class="sn-select po-product" required aria-label="Produk">
                        <option value="">— Pilih produk —</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->distributor_price ?: $p->price }}">{{ $p->name }} — Rp {{ number_format($p->distributor_price ?: $p->price, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="items[0][qty]" class="sn-input po-qty" min="1" value="1" aria-label="Jumlah" required>
                    <button type="button" class="sn-btn sn-btn-ghost sn-btn-sm po-del" aria-label="Hapus baris">✕</button>
                </div>
            </div>
            @error('items')<p class="sn-error">{{ $message }}</p>@enderror
            <button type="button" id="po-add" class="sn-btn sn-btn-ghost sn-btn-sm w-full md:w-auto">+ Tambah produk</button>
            <div>
                <label class="sn-label" for="po-proof">Bukti pembayaran (wajib bila DIKIRIM)</label>
                <input id="po-proof" type="file" name="payment_proof" class="sn-input @error('payment_proof') sn-input--error @enderror" accept="image/jpeg,image/png,image/webp">
                <p class="sn-help">Format JPG/PNG/WebP, maks 5 MB. Simpan struk transfer sebagai foto.</p>
                @error('payment_proof')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="sn-label" for="po-notes">Catatan (opsional)</label>
                <textarea id="po-notes" name="notes" class="sn-textarea" rows="2" placeholder="Contoh: butuh sebelum akhir pekan">{{ old('notes') }}</textarea>
                @error('notes')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center justify-between border-[3px] border-black rounded bg-[#FFD21F] px-4 py-3">
                <span class="font-mono text-xs font-bold uppercase tracking-widest">Total</span>
                <strong class="font-display text-xl" id="po-total">Rp 0</strong>
            </div>
            <button type="submit" class="sn-btn sn-btn-yellow sn-btn-lg w-full">KIRIM PO</button>
        </div>
    </form>
</details>

<h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Riwayat PO</h2>
<form method="GET" action="{{ route('distributor.po') }}" class="flex gap-2 mb-3">
    <select name="status" class="sn-select" onchange="this.form.submit()" aria-label="Saring status PO">
        <option value="">Semua status</option>
        @foreach ($statuses as $s)
            <option value="{{ $s }}" @selected(($filterStatus ?? '') === $s)>{{ strtoupper($s) }}</option>
        @endforeach
    </select>
    @if (! empty($filterStatus))
        <a href="{{ route('distributor.po') }}" class="sn-btn sn-btn-ghost sn-btn-sm">Reset</a>
    @endif
</form>

@if ($orders->isEmpty())
    <x-senyum-empty-state title="Belum ada permintaan" message="Stok menipis? Kirim permintaan pertamamu di atas." />
@else
    <div class="sn-table-wrap hidden md:block">
        <table class="sn-table">
            <thead><tr><th>Kode</th><th>Isi</th><th class="sn-num">Total</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach ($orders as $po)
                    <tr>
                        <td class="font-mono font-bold">{{ $po->code }}</td>
                        <td class="text-xs">{{ $po->items->map(fn ($i) => ($i->product?->name ?? $i->product_name ?? '?') . ' ×' . $i->qty)->join(', ') }}</td>
                        <td class="sn-num">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                        <td><x-senyum-badge :status="$po->status">{{ strtoupper($po->status) }}</x-senyum-badge></td>
                        <td>{{ $po->order_date?->format('d M Y') ?? $po->created_at->format('d M Y') }}</td>
                        <td><a href="{{ route('distributor.po.show', $po) }}" class="underline font-bold text-sm">Detail</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="grid gap-2 md:hidden">
        @foreach ($orders as $po)
            <div class="sn-card !p-3 text-sm">
                <div class="flex items-center gap-2"><strong class="font-mono">{{ $po->code }}</strong><x-senyum-badge :status="$po->status">{{ strtoupper($po->status) }}</x-senyum-badge></div>
                <span class="block text-xs text-neutral-500 mt-1">{{ $po->items->map(fn ($i) => ($i->product?->name ?? $i->product_name ?? '?') . ' ×' . $i->qty)->join(', ') }}</span>
                <div class="flex items-center justify-between mt-1">
                    <strong>Rp {{ number_format($po->total_amount, 0, ',', '.') }}</strong>
                    <a href="{{ route('distributor.po.show', $po) }}" class="underline font-bold">Detail</a>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
@endif

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">Status permintaan berubah? Cek <a class="underline font-bold" href="{{ route('distributor.notifikasi') }}">notifikasi</a> untuk kabar dari developer.</x-senyum-alert>
</div>

@push('scripts')
<script>
(function () {
    var wrap = document.getElementById('po-rows');
    var add = document.getElementById('po-add');
    var totalEl = document.getElementById('po-total');
    if (!wrap || !add || !totalEl) return;
    var idx = 1;
    function fmt(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }
    function recalc() {
        var total = 0;
        wrap.querySelectorAll('[data-row]').forEach(function (row) {
            var prod = row.querySelector('.po-product');
            var qty = row.querySelector('.po-qty');
            var base = 0;
            if (prod && prod.selectedOptions.length) {
                base = parseInt(prod.selectedOptions[0].getAttribute('data-price') || '0', 10) || 0;
            }
            var q = qty ? (parseInt(qty.value, 10) || 0) : 0;
            total += base * q;
        });
        totalEl.textContent = fmt(total);
    }
    wrap.addEventListener('input', recalc);
    wrap.addEventListener('change', recalc);
    wrap.addEventListener('click', function (e) {
        var del = e.target.closest('.po-del');
        if (!del) return;
        if (wrap.querySelectorAll('[data-row]').length <= 1) return;
        del.closest('[data-row]').remove();
        recalc();
    });
    add.addEventListener('click', function () {
        var clone = wrap.querySelector('[data-row]').cloneNode(true);
        clone.querySelectorAll('select, input').forEach(function (el) {
            if (el.name) el.name = el.name.replace(/items\[\d+\]/, 'items[' + idx + ']');
            if (el.classList.contains('po-qty')) el.value = 1;
            else if (el.tagName === 'SELECT') el.selectedIndex = 0;
        });
        wrap.appendChild(clone);
        idx++;
        recalc();
    });
    recalc();
})();
</script>
@endpush
@endsection
