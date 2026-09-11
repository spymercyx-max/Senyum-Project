@extends('layouts.distributor')

@section('title', 'Koreksi ' . ($transaction->code ?? 'Transaksi'))

@section('content')
<p class="sn-kicker">Sales &middot; Koreksi Transaksi</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1 font-mono">{{ $transaction->code }}</h1>
<p class="mt-1 text-sm text-neutral-600">Ubah item, harga, tanggal, atau pembeli. Selisih qty per produk dihitung otomatis (delta): kurang dikembalikan ke stok, lebih mengurangi stok.</p>

@if ($errors->any())
    <div class="mt-4"><x-senyum-alert type="danger" title="Belum bisa disimpan"><ul class="list-disc ml-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-senyum-alert></div>
@endif

<form method="POST" action="{{ route('distributor.transaksi.update', $transaction->id) }}" class="sn-card mt-5" id="trx-edit-form">
    @csrf
    @method('PUT')
    <div class="grid gap-4">
        <div>
            <label class="sn-label" for="te-buyer">Nama pembeli (opsional)</label>
            <input id="te-buyer" type="text" name="buyer_name" class="sn-input @error('buyer_name') sn-input--error @enderror" value="{{ old('buyer_name', $transaction->buyer_name) }}" placeholder="Contoh: pemilik outlet">
            @error('buyer_name')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="sn-label" for="te-date">Tanggal penjualan</label>
            <input id="te-date" type="date" name="sold_at" class="sn-input @error('sold_at') sn-input--error @enderror" value="{{ old('sold_at', $transaction->sold_at?->toDateString()) }}">
            @error('sold_at')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="sn-label" for="te-notes">Catatan (opsional)</label>
            <textarea id="te-notes" name="notes" rows="2" class="sn-textarea @error('notes') sn-textarea--error @enderror" placeholder="Contoh: koreksi qty">{{ old('notes', $transaction->notes) }}</textarea>
            @error('notes')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="sn-label" for="te-reason">Alasan koreksi</label>
            <textarea id="te-reason" name="correction_reason" rows="2" class="sn-textarea @error('correction_reason') sn-textarea--error @enderror" placeholder="Contoh: salah hitung qty saat input">{{ old('correction_reason') }}</textarea>
            @error('correction_reason')<p class="sn-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <span class="sn-label">Produk</span>
            <div id="te-rows" class="grid gap-2">
                @php $oldItems = old('items', $transaction->items->map(fn ($it) => ['product_id' => $it->product_id, 'qty' => $it->qty, 'price' => $it->price])->toArray()); @endphp
                @foreach ($oldItems as $i => $row)
                    <div class="border-2 border-black rounded-sm p-2 bg-white" data-row>
                        <div class="grid grid-cols-[1fr_70px_110px_auto] gap-2 items-start">
                            <select name="items[{{ $i }}][product_id]" class="sn-select te-product" required aria-label="Produk baris {{ $i + 1 }}">
                                <option value="">— Pilih produk —</option>
                                @foreach ($products as $p)
                                    <option value="{{ $p->id }}" data-price="{{ $p->price }}" @selected((string) ($row['product_id'] ?? '') === (string) $p->id)>{{ $p->name }} — Rp {{ number_format($p->price, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="items[{{ $i }}][qty]" class="sn-input te-qty" min="1" value="{{ $row['qty'] ?? 1 }}" aria-label="Jumlah baris {{ $i + 1 }}" required>
                            <input type="number" name="items[{{ $i }}][price]" class="sn-input te-price" min="0" value="{{ $row['price'] ?? '' }}" placeholder="Harga" aria-label="Harga baris {{ $i + 1 }}">
                            <button type="button" class="sn-btn sn-btn-ghost sn-btn-sm te-del" aria-label="Hapus baris {{ $i + 1 }}">✕</button>
                        </div>
                        <p class="text-xs text-right mt-1">Subtotal: <strong data-row-subtotal>Rp 0</strong></p>
                    </div>
                @endforeach
            </div>
            @error('items')<p class="sn-error">{{ $message }}</p>@enderror
            <button type="button" id="te-add" class="sn-btn sn-btn-ghost sn-btn-sm mt-2 w-full md:w-auto">+ Tambah baris</button>
        </div>

        <div class="flex items-center justify-between border-[3px] border-black rounded bg-[#FFD21F] px-4 py-3">
            <span class="font-mono text-xs font-bold uppercase tracking-widest">Total</span>
            <strong class="font-display text-xl" id="te-total">Rp 0</strong>
        </div>

        <div class="grid gap-2 sm:flex">
            <button type="submit" class="sn-btn sn-btn-yellow w-full sm:w-auto">SIMPAN KOREKSI</button>
            <a href="{{ route('distributor.transaksi.show', $transaction->id) }}" class="sn-btn sn-btn-ghost w-full sm:w-auto">Batal</a>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    var wrap = document.getElementById('te-rows');
    var add = document.getElementById('te-add');
    var totalEl = document.getElementById('te-total');
    if (!wrap || !add || !totalEl) return;
    var idx = wrap.querySelectorAll('[data-row]').length;
    function fmt(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }
    function rowSubtotal(row) {
        var prod = row.querySelector('.te-product');
        var qty = row.querySelector('.te-qty');
        var price = row.querySelector('.te-price');
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
        totalEl.textContent = fmt(total);
    }
    wrap.addEventListener('input', recalc);
    wrap.addEventListener('change', recalc);
    wrap.addEventListener('click', function (e) {
        var del = e.target.closest('.te-del');
        if (!del) return;
        if (wrap.querySelectorAll('[data-row]').length <= 1) return;
        del.closest('[data-row]').remove();
        recalc();
    });
    add.addEventListener('click', function () {
        var first = wrap.querySelector('[data-row]');
        var clone = first.cloneNode(true);
        clone.querySelectorAll('select, input').forEach(function (el) {
            if (el.name) el.name = el.name.replace(/items\[\d+\]/, 'items[' + idx + ']');
            if (el.classList.contains('te-qty')) el.value = 1;
            else if (el.classList.contains('te-price')) el.value = '';
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
