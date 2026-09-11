@extends('layouts.developer')

@section('title', 'Dashboard — SENYUM Command Center')

@section('content')
<div class="space-y-6">
    <div class="sn-card" style="background:#0FA3A3;border-color:#141414;">
        <p class="sn-kicker">Senyum Command Center · Identitas Tosca</p>
        <div class="flex flex-wrap items-end gap-3 mt-1">
            <h1 class="font-display font-black uppercase text-2xl sm:text-3xl leading-none text-black">Command Center</h1>
            <p class="font-mono text-xs uppercase tracking-widest text-black/80 ml-auto">{{ now()->translatedFormat('l, d M Y') }} · <span class="font-bold">Online</span></p>
        </div>
        <p class="text-sm text-black mt-2">Fokus hari ini: {{ $pendingPOCount }} PO menunggu · {{ $alertCount }} peringatan stok · {{ $pendingDistributorCount }} pendaftar baru.</p>
    </div>

    @if (session('success'))
        <x-senyum-alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-senyum-alert type="danger" :message="session('error')" />
    @endif

    <x-senyum-card title="PO DRAFT menunggu persetujuan ({{ $pendingPOCount }})" kicker="Prioritas · Purchase Order">
        @if ($pendingPOs->isEmpty())
            <x-senyum-empty-state title="Tidak ada antrean" message="Semua purchase order sudah diproses." />
        @else
            <div class="sn-table-wrap hidden md:block">
                <table class="sn-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Distributor</th>
                            <th>Mode</th>
                            <th>Item</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingPOs as $po)
                            <tr>
                                <td><a href="{{ route('developer.po.show', $po) }}" class="font-mono font-bold underline">{{ $po->code }}</a></td>
                                <td>{{ $po->distributor?->name ?? '—' }}</td>
                                <td class="font-mono text-xs">{{ strtoupper($po->fulfillment ?? '') }}</td>
                                <td class="sn-num">{{ $po->items->count() }}</td>
                                <td class="sn-num">Rp{{ number_format($po->total_amount, 0, ',', '.') }}</td>
                                <td><a href="{{ route('developer.po.show', $po) }}" class="sn-btn sn-btn-tosca sn-btn-sm">Proses</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="space-y-3 md:hidden">
                @foreach ($pendingPOs as $po)
                    <div class="sn-card !p-3">
                        <a href="{{ route('developer.po.show', $po) }}" class="font-mono font-bold text-sm underline">{{ $po->code }}</a>
                        <p class="text-xs mt-1">{{ $po->distributor?->name ?? '—' }} · {{ $po->items->count() }} item · Rp{{ number_format($po->total_amount, 0, ',', '.') }}</p>
                        <a href="{{ route('developer.po.show', $po) }}" class="sn-btn sn-btn-tosca sn-btn-sm mt-2">Proses</a>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('developer.po', ['status' => 'draft']) }}" class="sn-btn sn-btn-primary sn-btn-sm mt-4">Lihat semua PO DRAFT</a>
        @endif
    </x-senyum-card>

    <div class="grid lg:grid-cols-2 gap-6">
        <x-senyum-card title="Peringatan stok ({{ $alertCount }})" kicker="Inventory alerts">
            @if ($alerts->isEmpty())
                <x-senyum-empty-state title="Stok aman" message="Semua produk di atas ambang batas." />
            @else
                <ul class="space-y-2">
                    @foreach ($alerts as $inv)
                        <li class="flex items-center gap-2 border-2 border-black rounded-md px-3 py-2 bg-white flex-wrap">
                            <div class="min-w-0">
                                <p class="font-bold text-sm truncate">{{ $inv->product?->name ?? '—' }}</p>
                                <p class="font-mono text-xs text-neutral-500">Tersedia {{ $inv->available }} / ambang {{ $inv->threshold }}</p>
                            </div>
                            <span class="sn-badge ml-auto {{ $inv->available <= 0 ? 'sn-badge--danger' : 'sn-badge--pending' }}">{{ $inv->available <= 0 ? 'HABIS' : ($inv->available <= (int) ceil(max(0, $inv->threshold) * 0.5) ? 'KRITIS' : 'MENIPIS') }}</span>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('developer.inventory') }}" class="sn-btn sn-btn-yellow sn-btn-sm mt-4">Kelola inventory</a>
            @endif
        </x-senyum-card>

        <x-senyum-card title="Pendaftar distributor baru ({{ $pendingDistributorCount }})" kicker="Registrasi pending">
            @if ($pendingDistributors->isEmpty())
                <x-senyum-empty-state title="Tidak ada pendaftar" message="Pengajuan distributor baru akan muncul di sini." />
            @else
                <ul class="space-y-2">
                    @foreach ($pendingDistributors as $d)
                        <li class="flex items-center gap-2 border-2 border-black rounded-md px-3 py-2 bg-white flex-wrap">
                            <div class="min-w-0">
                                <a href="{{ route('developer.distributor.show', $d) }}" class="font-bold text-sm underline">{{ $d->name }}</a>
                                <p class="font-mono text-xs text-neutral-500">@{{ $d->username }} · {{ $d->created_at?->diffForHumans() }}</p>
                            </div>
                            <a href="{{ route('developer.distributor.show', $d) }}" class="sn-btn sn-btn-primary sn-btn-sm ml-auto">Tinjau</a>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('developer.distributor', ['status' => 'pending']) }}" class="sn-btn sn-btn-ghost sn-btn-sm mt-4">Lihat semua pendaftar</a>
            @endif
        </x-senyum-card>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <x-senyum-card title="Pertumbuhan territory" kicker="+{{ $newTerritories }} wilayah 30 hari terakhir">
            @if ($topTerritories->isEmpty())
                <x-senyum-empty-state title="Belum ada wilayah" message="Data wilayah akan muncul di sini." />
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($topTerritories as $t)
                        <li class="border-b-2 border-dashed border-neutral-300 pb-2">
                            <p><a href="{{ route('developer.wilayah.show', $t) }}" class="font-bold underline">{{ $t->city }} – {{ $t->district }}</a></p>
                            <p class="text-xs text-neutral-500">{{ $t->outlets_count }} outlet · {{ $t->distributor_profiles_count }} distributor · {{ $t->tx_count }} transaksi · Rp{{ number_format($t->tx_total, 0, ',', '.') }}</p>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('developer.wilayah') }}" class="sn-btn sn-btn-ghost sn-btn-sm mt-4">Lihat semua wilayah</a>
            @endif
        </x-senyum-card>

        <x-senyum-card title="Coverage ringkas" kicker="{{ $coverage['rate'] }}% area aktif">
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="border-2 border-black rounded-md p-3" style="background:#0FA3A3;">
                    <p class="font-display font-black text-2xl text-black">{{ $coverage['covered'] }}</p>
                    <p class="font-mono text-[11px] uppercase text-black">Covered</p>
                </div>
                <div class="border-2 border-black rounded-md p-3 bg-[#FFD21F]">
                    <p class="font-display font-black text-2xl">{{ $coverage['partial'] }}</p>
                    <p class="font-mono text-[11px] uppercase">Partial</p>
                </div>
                <div class="border-2 border-black rounded-md p-3 bg-white">
                    <p class="font-display font-black text-2xl">{{ $coverage['uncovered'] }}</p>
                    <p class="font-mono text-[11px] uppercase text-neutral-500">Uncovered</p>
                </div>
            </div>
            <p class="sn-help mt-2">Covered = punya outlet aktif + transaksi/kunjungan ≤30 hari. Total {{ $coverage['total'] }} wilayah.</p>
            <a href="{{ route('developer.peta') }}" class="sn-btn sn-btn-tosca sn-btn-sm mt-3">Buka Peta Strategis</a>
        </x-senyum-card>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <x-senyum-card title="Aktivitas terakhir" kicker="Recent activity">
            @if ($recentActivity->isEmpty())
                <x-senyum-empty-state title="Belum ada aktivitas" message="Jejak aktivitas tim akan tercatat di sini." />
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($recentActivity as $log)
                        <li class="border-b-2 border-dashed border-neutral-300 pb-2">
                            <p><strong>{{ $log->actor?->name ?? 'Sistem' }}</strong> · <span class="font-mono text-xs">{{ $log->action }}</span></p>
                            <p class="font-mono text-xs text-neutral-500">{{ $log->created_at?->diffForHumans() }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-senyum-card>

        <x-senyum-card title="Jalan pintas" kicker="Shortcuts">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('developer.produk.create') }}" class="sn-btn sn-btn-tosca sn-btn-sm">+ Tambah produk</a>
                <a href="{{ route('developer.produk') }}" class="sn-btn sn-btn-ghost sn-btn-sm">Kelola produk</a>
                <a href="{{ route('developer.peta') }}" class="sn-btn sn-btn-ghost sn-btn-sm">Peta Strategis</a>
                <a href="{{ route('developer.laporan') }}" class="sn-btn sn-btn-ghost sn-btn-sm">Laporan</a>
                <a href="{{ route('developer.inventory') }}" class="sn-btn sn-btn-ghost sn-btn-sm">Inventory</a>
            </div>
        </x-senyum-card>
    </div>
</div>
@endsection
