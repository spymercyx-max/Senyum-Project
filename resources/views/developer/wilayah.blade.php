@extends('layouts.developer')

@section('title', 'Wilayah — SENYUM Command Center')

@section('content')
<div class="space-y-6">
    <div>
        <p class="sn-kicker">Network · Wilayah</p>
        <h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Wilayah Distribusi</h1>
        <p class="text-sm text-neutral-600 mt-1">Sebaran kota, kecamatan, distributor, dan outlet SENYUM.</p>
    </div>

    @if (session('success'))
        <x-senyum-alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-senyum-alert type="danger" :message="session('error')" />
    @endif

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        <x-senyum-stat label="Total wilayah" :value="$metrics['total']" />
        <x-senyum-stat label="Ada distributor" :value="$metrics['withDistributor']" />
        <x-senyum-stat label="Outlet aktif" :value="$metrics['activeOutlet']" />
        <x-senyum-stat label="Aktivitas lapangan" :value="$metrics['activity']" sub="Kunjungan + transaksi" />
    </div>

    @if ($territories->isEmpty())
        <x-senyum-empty-state title="Belum ada wilayah" message="Wilayah distribusi akan muncul di sini setelah ditambahkan." />
    @else
        <div class="sn-table-wrap hidden md:block">
            <table class="sn-table">
                <thead>
                    <tr>
                        <th>Wilayah</th>
                        <th>Distributor</th>
                        <th>Outlet</th>
                        <th>Aktivitas</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($territories as $t)
                        <tr>
                            <td>
                                <a href="{{ route('developer.wilayah.show', $t) }}" class="font-bold underline">{{ $t->city }} – {{ $t->district }}</a>
                                @if ($t->code)
                                    <p class="font-mono text-xs text-neutral-500">{{ $t->code }}</p>
                                @endif
                                @if ($t->distributorProfiles->isNotEmpty())
                                    <p class="text-xs text-neutral-500">Distributor: {{ $t->distributorProfiles->map(fn ($p) => $p->user?->name)->filter()->join(', ') }}</p>
                                @endif
                            </td>
                            <td class="sn-num">{{ $t->distributor_profiles_count }}</td>
                            <td class="sn-num">{{ $t->outlets_count }}</td>
                            <td class="sn-num">{{ $t->tx_count ?? 0 }} trx</td>
                            <td><x-senyum-badge status="{{ $t->status === 'active' ? 'active' : 'suspended' }}">{{ strtoupper($t->status) }}</x-senyum-badge></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="space-y-3 md:hidden">
            @foreach ($territories as $t)
                <div class="sn-card">
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('developer.wilayah.show', $t) }}" class="font-display font-bold uppercase text-sm underline">{{ $t->city }} – {{ $t->district }}</a>
                        <x-senyum-badge status="{{ $t->status === 'active' ? 'active' : 'suspended' }}">{{ strtoupper($t->status) }}</x-senyum-badge>
                    </div>
                    <p class="text-xs mt-1">{{ $t->distributor_profiles_count }} distributor · {{ $t->outlets_count }} outlet · {{ $t->tx_count ?? 0 }} transaksi</p>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $territories->links() }}</div>
    @endif

    <livewire:developer.territory-board />
</div>
@endsection
