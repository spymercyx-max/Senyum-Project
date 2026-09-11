@extends('layouts.developer')

@section('title', 'Outlet — SENYUM Command Center')

@section('content')
<div class="space-y-6">
    <div>
        <p class="sn-kicker">Network · Outlet</p>
        <h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Outlet</h1>
        <p class="text-sm text-neutral-600 mt-1">Semua titik penjualan mitra di seluruh wilayah.</p>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        <x-senyum-stat label="Total outlet" :value="$counts['total']" />
        <x-senyum-stat label="Aktif" :value="$counts['active']" />
        <x-senyum-stat label="Nonaktif" :value="$counts['inactive']" />
        <x-senyum-stat label="Menunggu" :value="$counts['pending']" />
    </div>

    <form method="GET" action="{{ route('developer.outlet') }}" class="flex gap-2 max-w-md">
        <input type="search" name="q" value="{{ $q }}" class="sn-input" placeholder="Cari nama, kota, kecamatan…" aria-label="Cari outlet">
        <button type="submit" class="sn-btn sn-btn-primary sn-btn-sm shrink-0">Cari</button>
    </form>

    @if ($outlets->isEmpty())
        <x-senyum-empty-state title="Belum ada outlet" message="Outlet mitra akan muncul di sini." />
    @else
        <div class="sn-table-wrap hidden md:block">
            <table class="sn-table">
                <thead>
                    <tr>
                        <th>Outlet</th>
                        <th>Wilayah</th>
                        <th>Distributor</th>
                        <th>Kontak</th>
                        <th>Koordinat</th>
                        <th>Peta</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($outlets as $outlet)
                        <tr>
                            <td>
                                <p class="font-bold">{{ $outlet->name }}</p>
                                <p class="text-xs text-neutral-500">{{ $outlet->address }}</p>
                            </td>
                            <td>{{ $outlet->territory?->city ?? '—' }} – {{ $outlet->territory?->district ?? '' }}<p class="text-xs text-neutral-500">{{ $outlet->city }} · {{ $outlet->district }}</p></td>
                            <td>{{ $outlet->distributor?->name ?? '—' }}</td>
                            <td class="font-mono text-xs">{{ $outlet->phone ?? '—' }}</td>
                            <td class="font-mono text-xs">{{ $outlet->latitude && $outlet->longitude ? $outlet->latitude . ', ' . $outlet->longitude : '—' }}</td>
                            <td>
                                @if ($outlet->latitude && $outlet->longitude)
                                    <a href="https://www.google.com/maps?q={{ $outlet->latitude }},{{ $outlet->longitude }}" target="_blank" rel="noopener" class="sn-btn sn-btn-tosca sn-btn-sm">Buka Maps</a>
                                @else
                                    <span class="font-mono text-xs text-neutral-500">—</span>
                                @endif
                            </td>
                            <td><x-senyum-badge status="{{ $outlet->status === 'active' ? 'active' : ($outlet->status === 'pending' ? 'pending' : 'suspended') }}">{{ strtoupper($outlet->status) }}</x-senyum-badge></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="space-y-3 md:hidden">
            @foreach ($outlets as $outlet)
                <div class="sn-card">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-display font-bold uppercase text-sm">{{ $outlet->name }}</p>
                        <x-senyum-badge status="{{ $outlet->status === 'active' ? 'active' : 'pending' }}">{{ strtoupper($outlet->status) }}</x-senyum-badge>
                    </div>
                    <p class="text-xs mt-1">{{ $outlet->address }}</p>
                    <p class="text-xs mt-1">{{ $outlet->territory?->city ?? '—' }} · {{ $outlet->distributor?->name ?? '—' }}</p>
                    @if ($outlet->latitude && $outlet->longitude)
                        <p class="font-mono text-[11px] text-neutral-500 mt-1">{{ $outlet->latitude }}, {{ $outlet->longitude }}</p>
                        <a href="https://www.google.com/maps?q={{ $outlet->latitude }},{{ $outlet->longitude }}" target="_blank" rel="noopener" class="sn-btn sn-btn-tosca sn-btn-sm mt-3">Buka Maps</a>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $outlets->links() }}</div>
    @endif
</div>
@endsection
