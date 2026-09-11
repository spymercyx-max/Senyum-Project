@extends('layouts.distributor')

@section('title', 'Pembukuan — Field Workspace')

@section('content')
<p class="sn-kicker">Operations &middot; Pembukuan</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Pembukuan</h1>
<p class="mt-1 text-sm text-neutral-600">Stok milikmu saat ini dan ringkasan penjualan. Stok bertambah saat PO disetujui, berkurang saat kamu mencatat penjualan.</p>

<div class="sn-card sn-card-cyan mt-5">
    <p class="sn-kicker mb-2">Stok Distributor</p>
    @if (empty($summary['stockLines']))
        <p class="text-sm">Belum ada stok tercatat. <a class="underline font-bold" href="{{ route('distributor.po') }}">Minta stok (PO) →</a></p>
    @else
        <ul class="grid gap-1.5 text-sm">
            @foreach ($summary['stockLines'] as $line)
                <li class="flex items-center gap-2 bg-white border-2 border-black rounded px-3 py-2">
                    <span class="flex-1 font-bold">{{ $line['product']->name }}</span>
                    <span class="font-mono font-bold">{{ $line['qty'] }}</span>
                </li>
            @endforeach
        </ul>
        <p class="mt-3 font-display font-black uppercase">Total unit tersedia: {{ $summary['totalUnits'] }} pcs</p>
    @endif
</div>

<div class="mt-5 grid grid-cols-2 lg:grid-cols-4 gap-3">
    <x-senyum-stat label="Total Penjualan" :value="'Rp ' . number_format($summary['totalTrx'], 0, ',', '.')" sub="semua waktu" />
    <x-senyum-stat label="Transaksi Bulan Ini" :value="$summary['trxMonth']" sub="berdasar tanggal bisnis" />
    <x-senyum-stat label="Transaksi 7 Hari" :value="$summary['trx7']" sub="terakhir" />
    <x-senyum-stat label="PO Aktif" :value="$summary['poActive']" sub="belum selesai/ditolak" />
</div>

<h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Transaksi Terbaru</h2>
@if ($recentTrx->isEmpty())
    <x-senyum-empty-state title="Belum ada transaksi" message="Catatan penjualanmu akan diringkas di sini." action="Buat Pesanan" :href="route('distributor.pemesanan')" />
@else
    <div class="sn-table-wrap hidden md:block">
        <table class="sn-table">
            <thead><tr><th>Kode</th><th>Outlet</th><th class="sn-num">Total</th><th>Tanggal bisnis</th></tr></thead>
            <tbody>
                @foreach ($recentTrx as $t)
                    <tr><td class="font-mono font-bold">{{ $t->code }}</td><td>{{ $t->outlet?->name ?? '—' }}</td><td class="sn-num">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td><td>{{ $t->sold_at?->format('d M Y') ?? $t->created_at->format('d M Y') }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="grid gap-2 md:hidden">
        @foreach ($recentTrx as $t)
            <div class="sn-card !p-3 text-sm"><strong class="font-mono">{{ $t->code }}</strong><span class="block text-neutral-600">{{ $t->outlet?->name ?? '—' }} &middot; {{ $t->sold_at?->format('d M Y') ?? $t->created_at->format('d M Y') }}</span><strong class="block text-right">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</strong></div>
        @endforeach
    </div>
@endif

<h2 class="font-display font-black uppercase text-lg mt-7 mb-3">PO Terbaru</h2>
@if ($recentPo->isEmpty())
    <p class="text-sm text-neutral-500">Belum ada permintaan stok. <a class="underline font-bold" href="{{ route('distributor.po') }}">Minta stok →</a></p>
@else
    <div class="grid gap-2">
        @foreach ($recentPo as $po)
            <div class="sn-card !p-3 text-sm">
                <strong class="font-mono">{{ $po->code }}</strong>
                <x-senyum-badge :status="$po->status">{{ strtoupper($po->status) }}</x-senyum-badge>
                <span class="block mt-1"><strong>Rp {{ number_format($po->total_amount, 0, ',', '.') }}</strong> &middot; {{ $po->order_date?->format('d M Y') ?? $po->created_at->format('d M Y') }}</span>
            </div>
        @endforeach
    </div>
@endif

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">Cek halaman ini tiap malam: pastikan semua penjualan hari ini sudah tercatat.</x-senyum-alert>
</div>
@endsection
