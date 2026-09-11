@extends('layouts.distributor')

@section('title', 'Dashboard — Field Workspace')

@section('content')
<p class="sn-kicker">Senyum Field Workspace</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl leading-tight mt-1">Welcome to Senyum</h1>
<p class="mt-1 text-sm text-neutral-600">Halo, <strong class="text-black">{{ $user->name }}</strong> &middot; Wilayah: <strong class="text-black">{{ $territory ?? 'Belum dipasang' }}</strong></p>

<div class="sn-card sn-card-cyan mt-4 !p-4 text-sm" role="note">
    <strong class="font-display uppercase">Field Workspace</strong>
    <span class="block mt-1">Ruang kerja harianmu: jual ke outlet, kunjungi titik, minta stok lewat PO.</span>
</div>

@if (session('success'))
    <div class="mt-4"><x-senyum-alert type="success" :message="session('success')" /></div>
@endif

{{-- METRICS --}}
<div class="mt-5 grid grid-cols-2 lg:grid-cols-4 gap-3">
    <x-senyum-stat label="Outlet Aktif" :value="$metrics['activeOutlets']" :sub="'dari ' . $metrics['totalOutlets'] . ' outlet'" />
    <x-senyum-stat label="PO Menunggu" :value="$metrics['poPending']" :sub="'dari ' . $metrics['poTotal'] . ' permintaan'" />
    <x-senyum-stat label="Jual Bulan Ini" :value="$metrics['trxMonth']" sub="transaksi tercatat" />
    <x-senyum-stat label="Visit Minggu Ini" :value="$metrics['visitsWeek']" sub="kunjungan outlet" />
</div>

{{-- ONBOARDING --}}
@if ($showOnboarding)
    <section class="sn-card sn-card-yellow mt-5" aria-label="Langkah awal">
        <p class="sn-kicker">Next Steps 01–05</p>
        <h2 class="sn-card-title">Mulai Berjualan</h2>
        <ul class="mt-2 space-y-2 text-sm font-medium">
            @foreach ($checklist as $i => $step)
                <li class="flex items-start gap-2 bg-white border-2 border-black rounded px-3 py-2">
                    <span class="font-mono font-bold">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="flex-1">{{ $step['label'] }}</span>
                    <span class="font-mono text-[11px] font-bold uppercase tracking-widest border-2 border-black rounded-sm px-2 py-0.5 {{ $step['done'] ? 'bg-black text-white' : 'bg-white' }}">{{ $step['done'] ? 'Done' : 'Todo' }}</span>
                </li>
            @endforeach
        </ul>
        <div class="mt-3 grid gap-2 sm:flex">
            <a href="{{ route('distributor.outlet.create') }}" class="sn-btn sn-btn-primary sn-btn-sm w-full sm:w-auto">+ Tambah Outlet</a>
            <a href="{{ route('distributor.pemesanan') }}" class="sn-btn sn-btn-ghost sn-btn-sm w-full sm:w-auto">Buat Pesanan</a>
        </div>
    </section>
@endif

{{-- ACTION TODAY --}}
<h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Action Today</h2>
<div class="grid gap-3 lg:grid-cols-2">
    <div class="sn-card">
        <p class="sn-kicker mb-2">Perlu Follow-Up</p>
        @forelse ($followUps as $v)
            <a href="{{ route('distributor.outlet.show', $v->outlet) }}" class="block border-2 border-black rounded px-3 py-2 mb-2 bg-[#FFF9EC] hover:bg-[#FFD21F]">
                <strong>{{ $v->outlet?->name ?? '—' }}</strong>
                <span class="block text-xs text-neutral-600">Tindak lanjut: {{ $v->follow_up_at?->format('d M Y') }} &middot; {{ $v->notes ? \Str::limit($v->notes, 60) : 'tanpa catatan' }}</span>
            </a>
        @empty
            <p class="text-sm text-neutral-500">Tidak ada yang mendesak.</p>
        @endforelse
    </div>
    <div class="sn-card">
        <p class="sn-kicker mb-2">PO Menunggu Diproses</p>
        @forelse ($pendingPos as $po)
            <a href="{{ route('distributor.po.show', $po) }}" class="block border-2 border-black rounded px-3 py-2 mb-2 bg-[#FFF9EC] hover:bg-[#FFD21F]">
                <strong class="font-mono">{{ $po->code }}</strong>
                <span class="block text-xs text-neutral-600"><x-senyum-badge :status="$po->status">{{ $po->status }}</x-senyum-badge> &middot; Rp {{ number_format($po->total_amount, 0, ',', '.') }}</span>
            </a>
        @empty
            <p class="text-sm text-neutral-500">Semua permintaan stok sudah diproses.</p>
        @endforelse
    </div>
    <div class="sn-card">
        <p class="sn-kicker mb-2">Visit Terjadwal</p>
        @forelse ($scheduled as $v)
            <div class="border-2 border-black rounded px-3 py-2 mb-2 bg-[#FFF9EC] text-sm">
                <strong>{{ $v->outlet?->name ?? '—' }}</strong>
                <span class="block text-xs text-neutral-600">{{ $v->visited_at?->format('d M Y') }}</span>
            </div>
        @empty
            <p class="text-sm text-neutral-500">Belum ada jadwal. <a class="underline font-bold" href="{{ route('distributor.visit') }}">Catat kunjungan →</a></p>
        @endforelse
    </div>
    <div class="sn-card sn-card-cyan">
        <p class="sn-kicker mb-2">Aksi Cepat</p>
        <div class="grid gap-2">
            <a href="{{ route('distributor.pemesanan') }}" class="sn-btn sn-btn-yellow sn-btn-sm w-full">Buat Pesanan</a>
            <a href="{{ route('distributor.jual-ecer') }}" class="sn-btn sn-btn-ghost sn-btn-sm w-full">Catat Jual Ecer</a>
            <a href="{{ route('distributor.po') }}" class="sn-btn sn-btn-ghost sn-btn-sm w-full">Minta Stok (PO)</a>
        </div>
    </div>
</div>

{{-- RECENT --}}
<h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Penjualan Terakhir</h2>
@if ($recentTrx->isEmpty())
    <x-senyum-empty-state title="Belum ada penjualan" message="Catat penjualan pertamamu ke outlet hari ini." action="Buat Pesanan" :href="route('distributor.pemesanan')" />
@else
    <div class="sn-table-wrap hidden md:block">
        <table class="sn-table">
            <thead><tr><th>Kode</th><th>Outlet</th><th class="sn-num">Total</th><th>Tanggal</th></tr></thead>
            <tbody>
                @foreach ($recentTrx as $t)
                    <tr><td class="font-mono font-bold">{{ $t->code }}</td><td>{{ $t->outlet?->name ?? '—' }}</td><td class="sn-num">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td><td>{{ $t->sold_at?->format('d M Y') ?? $t->created_at->format('d M Y') }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="grid gap-2 md:hidden">
        @foreach ($recentTrx as $t)
            <div class="sn-card !p-3 text-sm">
                <strong class="font-mono">{{ $t->code }}</strong>
                <span class="block text-neutral-600">{{ $t->outlet?->name ?? '—' }} &middot; {{ $t->sold_at?->format('d M Y') ?? $t->created_at->format('d M Y') }}</span>
                <strong>Rp {{ number_format($t->total_amount, 0, ',', '.') }}</strong>
            </div>
        @endforeach
    </div>
@endif

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">
        @if ($metrics['totalOutlets'] === 0)
            Tambah outlet pertamamu, lalu catat kunjungan dan penjualan.
        @elseif ($metrics['poPending'] > 0)
            Pantau {{ $metrics['poPending'] }} permintaan stok yang menunggu diproses.
        @elseif ($followUps->isNotEmpty())
            Ada {{ $followUps->count() }} outlet yang perlu ditindaklanjuti hari ini.
        @else
            Kerja bagus! Catat pesanan berikutnya atau kunjungi outlet.
        @endif
    </x-senyum-alert>
</div>
@endsection
