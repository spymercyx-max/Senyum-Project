@extends('layouts.distributor')

@section('title', 'Jual Ecer — Field Workspace')

@section('content')
<p class="sn-kicker">Sales &middot; Jual Ecer</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Jual Eceran</h1>
<p class="mt-1 text-sm text-neutral-600">Jual langsung ke pembeli akhir. Stok berkurang dari stok distributormu. Tanggal yang disimpan adalah tanggal bisnis.</p>

@if (session('success'))
    <div class="mt-4"><x-senyum-alert type="success" :message="session('success')" /></div>
@endif
@if ($errors->any())
    <div class="mt-4"><x-senyum-alert type="danger" title="Belum bisa disimpan"><ul class="list-disc ml-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-senyum-alert></div>
@endif

<form method="POST" action="{{ route('distributor.jual-ecer.store') }}" class="sn-card mt-5" id="ecer-form">
    @csrf
    <div class="grid gap-4">
        <div>
            <label class="sn-label" for="ecer-buyer">Nama Pembeli</label>
            <input id="ecer-buyer" type="text" name="buyer_name" class="sn-input @error('buyer_name') sn-input--error @enderror" value="{{ old('buyer_name') }}" placeholder="Contoh: Ibu Sari (opsional)">
            @error('buyer_name')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <label class="sn-label" for="ecer-outlet">Outlet sumber barang</label>
                <select id="ecer-outlet" name="outlet_id" class="sn-select @error('outlet_id') sn-select--error @enderror" required>
                    <option value="">— Pilih outlet —</option>
                    @foreach ($outlets as $o)
                        <option value="{{ $o->id }}" @selected(old('outlet_id') == $o->id)>{{ $o->name }}</option>
                    @endforeach
                </select>
                @error('outlet_id')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="sn-label" for="ecer-date">Tanggal penjualan</label>
                <input id="ecer-date" type="date" name="sold_at" class="sn-input @error('sold_at') sn-input--error @enderror" value="{{ old('sold_at', today()->toDateString()) }}">
                @error('sold_at')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="sn-label" for="ecer-notes">Catatan (opsional)</label>
            <input id="ecer-notes" type="text" name="notes" class="sn-input" value="{{ old('notes') }}" placeholder="Contoh: pembeli langganan">
            @error('notes')<p class="sn-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <span class="sn-label">Produk terjual</span>
            <div id="ecer-rows" class="grid gap-2">
                <div class="grid grid-cols-[1fr_70px_110px_auto] gap-2 items-start" data-row>
                    <select name="items[0][product_id]" class="sn-select ecer-product" required aria-label="Produk">
                        <option value="">— Pilih produk —</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->name }} — Rp {{ number_format($p->price, 0, ',', '.') }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="items[0][qty]" class="sn-input ecer-qty" min="1" value="1" aria-label="Jumlah" required>
                    <input type="number" name="items[0][price]" class="sn-input ecer-price" min="0" placeholder="Harga" aria-label="Harga per pcs">
                    <button type="button" class="sn-btn sn-btn-ghost sn-btn-sm ecer-del" aria-label="Hapus baris">✕</button>
                </div>
            </div>
            <p class="sn-help mt-1">Kosongkan kolom harga untuk memakai harga normal. Subtotal = qty × harga.</p>
            @error('items')<p class="sn-error">{{ $message }}</p>@enderror
            <button type="button" id="ecer-add" class="sn-btn sn-btn-ghost sn-btn-sm mt-2 w-full md:w-auto">+ Tambah baris</button>
        </div>

        <div class="flex items-center justify-between border-[3px] border-black rounded bg-[#FFD21F] px-4 py-3">
            <span class="font-mono text-xs font-bold uppercase tracking-widest">Total</span>
            <strong class="font-display text-xl" id="ecer-total">Rp 0</strong>
        </div>

        <button type="submit" class="sn-btn sn-btn-yellow sn-btn-lg w-full">CATAT PENJUALAN</button>
    </div>
</form>

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">Lihat semua penjualanmu di <a class="underline font-bold" href="{{ route('distributor.transaksi') }}">riwayat transaksi</a>.</x-senyum-alert>
</div>

@push('scripts')
<script>
(function () {
    var wrap = document.getElementById('ecer-rows');
    var add = document.getElementById('ecer-add');
    var totalEl = document.getElementById('ecer-total');
    if (!wrap || !add || !totalEl) return;
    var idx = 1;

    function fmt(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    }
    function rowSubtotal(row) {
        var prod = row.querySelector('.ecer-product');
        var qty = row.querySelector('.ecer-qty');
        var price = row.querySelector('.ecer-price');
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
            total += rowSubtotal(row);
        });
        totalEl.textContent = fmt(total);
    }
    wrap.addEventListener('input', recalc);
    wrap.addEventListener('change', function (e) {
        var prod = e.target.closest ? e.target.closest('.ecer-product') : null;
        if (prod) {
            var row = prod.closest('[data-row]');
            var price = row ? row.querySelector('.ecer-price') : null;
            if (row && price && price.value === '' && prod.selectedOptions.length) {
                price.value = prod.selectedOptions[0].getAttribute('data-price') || '';
            }
        }
        recalc();
    });
    wrap.addEventListener('click', function (e) {
        var del = e.target.closest('.ecer-del');
        if (!del) return;
        var rows = wrap.querySelectorAll('[data-row]');
        if (rows.length <= 1) return;
        del.closest('[data-row]').remove();
        recalc();
    });
    add.addEventListener('click', function () {
        var first = wrap.querySelector('[data-row]');
        var clone = first.cloneNode(true);
        clone.querySelectorAll('select, input').forEach(function (el) {
            if (el.name) el.name = el.name.replace(/items\[\d+\]/, 'items[' + idx + ']');
            if (el.classList.contains('ecer-qty')) el.value = 1;
            else if (el.classList.contains('ecer-price')) el.value = '';
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
