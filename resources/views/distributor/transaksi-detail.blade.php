@extends('layouts.distributor')

@section('title', ($transaction->code ?? 'Transaksi') . ' — Detail Transaksi')

@section('content')
<p class="sn-kicker">Sales &middot; Detail Transaksi</p>
<div class="flex flex-wrap items-center gap-2 mt-1">
    <h1 class="font-display font-black uppercase text-2xl sm:text-3xl font-mono">{{ $transaction->code }}</h1>
    <x-senyum-badge :status="$transaction->type ?? 'outlet'">{{ strtoupper($transaction->type ?? 'outlet') }}</x-senyum-badge>
    <x-senyum-badge :status="$transaction->status ?? 'completed'">{{ ($transaction->status ?? '') === 'void' ? 'DIBATALKAN' : strtoupper($transaction->status ?? 'completed') }}</x-senyum-badge>
</div>

@if (session('success'))
    <div class="mt-4"><x-senyum-alert type="success" :message="session('success')" /></div>
@endif
@if ($errors->any())
    <div class="mt-4"><x-senyum-alert type="danger" title="Belum bisa diproses"><ul class="list-disc ml-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-senyum-alert></div>
@endif

<div class="grid gap-3 mt-5 lg:grid-cols-2">
    <div class="sn-card">
        <p class="sn-kicker mb-2">Outlet</p>
        <p class="font-bold">{{ $transaction->outlet?->name ?? '—' }}</p>
        <p class="text-sm text-neutral-600">{{ $transaction->outlet?->address ?? '—' }}</p>
        <p class="text-sm text-neutral-600">{{ $transaction->outlet?->city ?? '—' }} &middot; Kec. {{ $transaction->outlet?->district ?? '—' }}</p>
        @if (! empty($territory))
            <p class="text-xs text-neutral-500 mt-1">Wilayah: {{ $territory }}</p>
        @endif
    </div>
    <div class="sn-card sn-card-cyan">
        <p class="sn-kicker mb-2">Transaksi</p>
        <dl class="text-sm space-y-1.5">
            <div><dt class="sn-label !mb-0">Kode</dt><dd class="font-mono font-bold">{{ $transaction->code }}</dd></div>
            <div><dt class="sn-label !mb-0">Tanggal bisnis</dt><dd>{{ $transaction->sold_at?->format('d M Y') ?? $transaction->created_at->format('d M Y') }}</dd></div>
            <div><dt class="sn-label !mb-0">Pembeli</dt><dd>{{ $transaction->buyer_name ?? '—' }}</dd></div>
            <div><dt class="sn-label !mb-0">Tipe</dt><dd>{{ strtoupper($transaction->type ?? 'outlet') }}</dd></div>
        </dl>
    </div>
</div>

<h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Item</h2>
<div class="sn-table-wrap hidden md:block">
    <table class="sn-table">
        <thead><tr><th>Produk</th><th class="sn-num">Qty</th><th class="sn-num">Harga beli (ref)</th><th class="sn-num">Harga jual</th><th class="sn-num">Subtotal</th></tr></thead>
        <tbody>
            @foreach ($transaction->items as $item)
                <tr>
                    <td><p class="font-bold">{{ $item->product?->name ?? $item->product_name ?? '—' }}</p><p class="font-mono text-xs text-neutral-500">{{ $item->sku ?? '—' }}</p></td>
                    <td class="sn-num">{{ $item->qty }}</td>
                    <td class="sn-num">Rp {{ number_format($item->cost_price ?? 0, 0, ',', '.') }}</td>
                    <td class="sn-num">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="sn-num">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="grid gap-2 md:hidden">
    @foreach ($transaction->items as $item)
        <div class="sn-card !p-3 text-sm">
            <strong>{{ $item->product?->name ?? $item->product_name ?? '—' }}</strong>
            <span class="block font-mono text-xs text-neutral-500">{{ $item->sku ?? '—' }}</span>
            <span class="block text-xs mt-1">Qty: {{ $item->qty }} &middot; Beli ref: Rp {{ number_format($item->cost_price ?? 0, 0, ',', '.') }} &middot; Jual: Rp {{ number_format($item->price, 0, ',', '.') }}</span>
            <strong class="block mt-1 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
        </div>
    @endforeach
</div>
<div class="flex items-center justify-between border-[3px] border-black rounded bg-[#FFD21F] px-4 py-3 mt-3">
    <span class="font-mono text-xs font-bold uppercase tracking-widest">Total</span>
    <strong class="font-display text-xl">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong>
</div>

@if ($transaction->notes)
    <div class="sn-card mt-3">
        <p class="sn-kicker mb-1">Catatan</p>
        <p class="text-sm">{{ $transaction->notes }}</p>
    </div>
@endif

<h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Riwayat</h2>
@if ($history->isEmpty())
    <p class="text-sm text-neutral-500">Belum ada riwayat.</p>
@else
    <div class="grid gap-2">
        @foreach ($history as $h)
            <div class="sn-card !p-3 text-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <strong>{{ $h->actor?->name ?? 'Sistem' }}</strong>
                    <span class="font-mono text-xs bg-black text-white px-2 py-0.5 rounded">{{ $h->action }}</span>
                    <span class="text-xs text-neutral-500 ml-auto">{{ $h->created_at->format('d M Y H:i') }}</span>
                </div>
                @if (! empty($h->metadata))
                    <pre class="font-mono text-xs bg-neutral-100 border border-neutral-300 rounded p-2 mt-2 overflow-x-auto">{{ json_encode($h->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @endif
            </div>
        @endforeach
    </div>
@endif

<div class="flex flex-wrap gap-2 mt-6">
    <a href="{{ route('distributor.transaksi') }}" class="sn-btn sn-btn-ghost sn-btn-sm">← Kembali</a>
    @if (($transaction->status ?? '') !== 'void')
        <a href="{{ route('distributor.transaksi.edit', $transaction->id) }}" class="sn-btn sn-btn-yellow sn-btn-sm">Edit</a>
        <button type="button" data-modal-open="#modal-void" class="sn-btn sn-btn-danger sn-btn-sm">Batalkan / Void</button>
    @endif
</div>

@if (($transaction->status ?? '') !== 'void')
    <div id="modal-void" class="sn-modal-backdrop" role="dialog" aria-modal="true" aria-label="Konfirmasi void {{ $transaction->code }}">
        <div class="sn-modal">
            <h3 class="sn-modal-title">Batalkan transaksi?</h3>
            <p class="text-sm leading-relaxed">Seluruh qty <strong>{{ $transaction->code }}</strong> dikembalikan ke stok. Tindakan tercatat di riwayat dan tidak bisa dibatalkan.</p>
            <form method="POST" action="{{ route('distributor.transaksi.void', $transaction->id) }}" class="mt-4 grid gap-3">
                @csrf
                <div>
                    <label class="sn-label" for="void-reason">Alasan pembatalan</label>
                    <textarea id="void-reason" name="reason" rows="2" class="sn-textarea" placeholder="Contoh: salah input outlet"></textarea>
                    @error('void')<p class="sn-error">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-2 justify-end">
                    <button type="button" data-modal-close class="sn-btn sn-btn-ghost sn-btn-sm">Batal</button>
                    <button type="submit" class="sn-btn sn-btn-danger sn-btn-sm">Ya, batalkan</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
