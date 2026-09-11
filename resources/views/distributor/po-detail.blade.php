@extends('layouts.distributor')

@section('title', ($order->code ?? 'PO') . ' — Detail PO')

@section('content')
<p class="sn-kicker">Operations &middot; Detail PO</p>
<div class="flex flex-wrap items-center gap-2 mt-1">
    <h1 class="font-display font-black uppercase text-2xl sm:text-3xl font-mono">{{ $order->code }}</h1>
    <x-senyum-badge :status="$order->status">{{ strtoupper($order->status) }}</x-senyum-badge>
    <x-senyum-badge status="info">{{ $order->fulfillment === 'delivery' ? 'DIKIRIM' : 'PICK-UP' }}</x-senyum-badge>
</div>

@if (session('success'))
    <div class="mt-4"><x-senyum-alert type="success" :message="session('success')" /></div>
@endif

<div class="grid gap-3 mt-5 lg:grid-cols-2">
    <div class="sn-card">
        <p class="sn-kicker mb-2">Ringkasan</p>
        <dl class="text-sm space-y-1.5">
            <div><dt class="sn-label !mb-0">Tanggal pemesanan</dt><dd>{{ $order->order_date?->format('d M Y') ?? $order->created_at->format('d M Y') }}</dd></div>
            <div><dt class="sn-label !mb-0">Mode pemenuhan</dt><dd class="font-bold">{{ $order->fulfillment === 'delivery' ? 'DIKIRIM (diantar)' : 'PICK-UP (diambil sendiri)' }}</dd></div>
            <div><dt class="sn-label !mb-0">Total</dt><dd class="font-display font-black text-lg">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</dd></div>
            @if ($order->notes)<div><dt class="sn-label !mb-0">Catatan</dt><dd>{{ $order->notes }}</dd></div>@endif
            @if ($order->payment_proof)<div><dt class="sn-label !mb-0">Bukti pembayaran</dt><dd><a href="{{ \Illuminate\Support\Facades\Storage::url($order->payment_proof) }}" target="_blank" rel="noopener" class="underline font-bold">Lihat bukti</a></dd></div>@endif
        </dl>
    </div>
    <div class="sn-card sn-card-cyan">
        <p class="sn-kicker mb-2">Status Pengiriman / Pengambilan</p>
        @if ($order->fulfillment === 'delivery')
            @if ($order->tracking_number)
                <p class="text-sm"><span class="sn-label !mb-0 block">Nomor resi</span><strong class="font-mono">{{ $order->tracking_number }}</strong></p>
            @else
                <p class="text-sm text-neutral-600">Resi belum tersedia — menunggu developer mengirim.</p>
            @endif
            @if ($order->delivery_note)<p class="text-sm mt-2"><span class="sn-label !mb-0 block">Catatan pengiriman</span>{{ $order->delivery_note }}</p>@endif
        @else
            @if ($order->pickup_message)
                <p class="text-sm"><span class="sn-label !mb-0 block">Info pengambilan</span>{{ $order->pickup_message }}</p>
            @else
                <p class="text-sm text-neutral-600">Info pengambilan belum tersedia — pantau notifikasi.</p>
            @endif
        @endif
        @if ($order->status === 'ditolak' && $order->rejection_reason)
            <div class="mt-3 bg-white border-2 border-black rounded-sm px-3 py-2">
                <p class="sn-label !mb-0">Alasan penolakan</p>
                <p class="text-sm">{{ $order->rejection_reason }}</p>
            </div>
        @endif
    </div>
</div>

<h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Items</h2>
<div class="sn-table-wrap hidden md:block">
    <table class="sn-table">
        <thead><tr><th>Produk</th><th class="sn-num">Qty</th><th class="sn-num">Harga</th><th class="sn-num">Subtotal</th></tr></thead>
        <tbody>
            @foreach ($order->items as $it)
                <tr>
                    <td>{{ $it->product?->name ?? $it->product_name ?? '—' }}</td>
                    <td class="sn-num">{{ $it->qty }}</td>
                    <td class="sn-num">Rp {{ number_format($it->price, 0, ',', '.') }}</td>
                    <td class="sn-num">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="grid gap-2 md:hidden">
    @foreach ($order->items as $it)
        <div class="sn-card !p-3 text-sm">
            <strong>{{ $it->product?->name ?? $it->product_name ?? '—' }}</strong>
            <span class="block text-neutral-600">{{ $it->qty }} × Rp {{ number_format($it->price, 0, ',', '.') }}</span>
            <strong class="block text-right">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</strong>
        </div>
    @endforeach
</div>

<h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Timeline</h2>
@if ($histories->isEmpty())
    <p class="text-sm text-neutral-500">Belum ada riwayat status.</p>
@else
    <ol class="grid gap-2">
        @foreach ($histories as $h)
            <li class="sn-card !p-3 text-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <x-senyum-badge :status="$h->to ?? 'draft'">{{ strtoupper($h->from ? $h->from . ' → ' . $h->to : ($h->to ?? '-')) }}</x-senyum-badge>
                    <time class="font-mono text-[11px] text-neutral-500 ml-auto">{{ $h->created_at->format('d M Y H:i') }}</time>
                </div>
                @if ($h->note)<p class="text-neutral-600 mt-1">{{ $h->note }}</p>@endif
                @if ($h->actor)<p class="text-xs text-neutral-500 mt-1">oleh {{ $h->actor->name }}</p>@endif
            </li>
        @endforeach
    </ol>
@endif

<div class="mt-5 flex flex-wrap gap-2">
    <a href="{{ route('distributor.po') }}" class="sn-btn sn-btn-ghost sn-btn-sm">← Semua PO</a>
    <a href="{{ route('distributor.notifikasi') }}" class="sn-btn sn-btn-ghost sn-btn-sm">Cek Notifikasi</a>
</div>
@endsection
