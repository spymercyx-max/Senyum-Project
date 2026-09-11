@extends('layouts.developer')

@section('title', $territory->city . ' – ' . $territory->district . ' — SENYUM Command Center')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('developer.wilayah') }}" class="sn-btn sn-btn-ghost sn-btn-sm mb-3">← Semua wilayah</a>
        <p class="sn-kicker">Network · Detail wilayah</p>
        <div class="flex flex-wrap items-center gap-3 mt-1">
            <h1 class="font-display font-black uppercase text-2xl sm:text-3xl">{{ $territory->city }} – {{ $territory->district }}</h1>
            <x-senyum-badge status="{{ $territory->status === 'active' ? 'active' : 'suspended' }}">{{ strtoupper($territory->status) }}</x-senyum-badge>
            <span class="sn-badge {{ $coverage === 'covered' ? 'sn-badge--disetujui' : ($coverage === 'partial' ? 'sn-badge--diambil' : 'sn-badge--draft') }}">COVERAGE: {{ strtoupper($coverage) }}</span>
        </div>
        @if ($territory->code)
            <p class="font-mono text-xs text-neutral-500 mt-1">Kode: {{ $territory->code }}</p>
        @endif
        @if ($territory->notes)
            <p class="text-sm text-neutral-600 mt-1">{{ $territory->notes }}</p>
        @endif
    </div>

    @if (session('success'))
        <x-senyum-alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-senyum-alert type="danger" :message="session('error')" />
    @endif

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        <x-senyum-stat label="Distributor" :value="$territory->distributor_profiles_count" />
        <x-senyum-stat label="Outlet aktif" :value="$activeOutletCount" :sub="'dari ' . $outletTotal . ' outlet'" />
        <x-senyum-stat label="Omzet wilayah" :value="'Rp' . number_format($txTotal, 0, ',', '.')" />
        <x-senyum-stat label="Kunjungan" :value="$visitTotal" />
    </div>

    <x-senyum-card title="Coverage & aktivitas" kicker="Coverage detail">
        <dl class="text-sm space-y-2">
            <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-40 shrink-0 text-neutral-500">Status coverage</dt><dd class="font-bold">{{ strtoupper($coverage) }}</dd></div>
            <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-40 shrink-0 text-neutral-500">Outlet aktif</dt><dd>{{ $activeOutletCount }} dari {{ $outletTotal }}</dd></div>
            <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-40 shrink-0 text-neutral-500">Transaksi (5 terakhir)</dt><dd>{{ $transactions->count() }} ditampilkan · total omzet Rp{{ number_format($txTotal, 0, ',', '.') }}</dd></div>
            <div class="flex gap-2"><dt class="font-mono text-xs uppercase w-40 shrink-0 text-neutral-500">Kunjungan (5 terakhir)</dt><dd>{{ $visits->count() }} ditampilkan · total {{ $visitTotal }}</dd></div>
        </dl>
        <p class="sn-help mt-2">Covered = punya outlet aktif + transaksi/kunjungan ≤30 hari. Partial = ada outlet tapi tanpa aktivitas terkini. Uncovered = tanpa distributor/outlet.</p>
    </x-senyum-card>

    <x-senyum-card title="Distributor di wilayah ini" kicker="Distributors">
        @if ($distributors->isEmpty())
            <x-senyum-empty-state title="Belum ada distributor" message="Tempatkan distributor lewat panel di bawah." />
        @else
            <ul class="space-y-2">
                @foreach ($distributors as $dp)
                    <li class="flex items-center gap-2 border-2 border-black rounded-md px-3 py-2 bg-white flex-wrap">
                        <div class="min-w-0">
                            @if ($dp->user)
                                <a href="{{ route('developer.distributor.show', $dp->user) }}" class="font-bold text-sm underline">{{ $dp->user->name }}</a>
                            @else
                                <p class="font-bold text-sm">—</p>
                            @endif
                            <p class="font-mono text-xs text-neutral-500">{{ '@' . ($dp->user?->username ?? '—') }}</p>
                        </div>
                        <x-senyum-badge status="{{ $dp->status === 'approved' ? 'approved' : 'pending' }}" class="ml-auto">{{ strtoupper($dp->status) }}</x-senyum-badge>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-senyum-card>

    <x-senyum-card title="Outlet" kicker="Outlets">
        @if ($outlets->isEmpty())
            <x-senyum-empty-state title="Belum ada outlet" message="Outlet di wilayah ini akan muncul di sini." />
        @else
            <div class="sn-table-wrap hidden md:block">
                <table class="sn-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Distributor</th>
                            <th>Koordinat</th>
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
                                <td>{{ $outlet->distributor?->name ?? '—' }}</td>
                                <td class="font-mono text-xs">{{ $outlet->latitude && $outlet->longitude ? $outlet->latitude . ', ' . $outlet->longitude : '—' }}</td>
                                <td><x-senyum-badge status="{{ $outlet->status === 'active' ? 'active' : ($outlet->status === 'pending' ? 'pending' : 'suspended') }}">{{ strtoupper($outlet->status) }}</x-senyum-badge></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="space-y-3 md:hidden">
                @foreach ($outlets as $outlet)
                    <div class="sn-card !p-3">
                        <p class="font-bold text-sm">{{ $outlet->name }}</p>
                        <p class="text-xs mt-1">{{ $outlet->distributor?->name ?? '—' }}</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $outlets->links() }}</div>
        @endif
    </x-senyum-card>

    <div class="grid lg:grid-cols-2 gap-6">
        <x-senyum-card title="Transaksi terakhir" kicker="Transactions">
            @if ($transactions->isEmpty())
                <x-senyum-empty-state title="Belum ada transaksi" message="Transaksi di wilayah ini akan muncul di sini." />
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($transactions as $tx)
                        <li class="border-b-2 border-dashed border-neutral-300 pb-2">
                            <p><strong class="font-mono">{{ $tx->code }}</strong> · {{ $tx->outlet?->name ?? '—' }}</p>
                            <p class="text-xs text-neutral-500">Rp{{ number_format($tx->total_amount, 0, ',', '.') }} · {{ $tx->distributor?->name ?? '—' }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-senyum-card>

        <x-senyum-card title="Kunjungan terakhir" kicker="Visits">
            @if ($visits->isEmpty())
                <x-senyum-empty-state title="Belum ada kunjungan" message="Kunjungan ke outlet akan muncul di sini." />
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($visits as $visit)
                        <li class="border-b-2 border-dashed border-neutral-300 pb-2">
                            <p><strong>{{ $visit->outlet?->name ?? '—' }}</strong> · {{ $visit->distributor?->name ?? '—' }}</p>
                            <p class="font-mono text-xs text-neutral-500">{{ $visit->visited_at?->translatedFormat('d M Y H:i') ?? '—' }}</p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-senyum-card>
    </div>

    <x-senyum-card title="Jejak aktivitas" kicker="Activity">
        @if ($activity->isEmpty())
            <x-senyum-empty-state title="Belum ada aktivitas" message="Aktivitas terkait wilayah ini akan tercatat di sini." />
        @else
            <ul class="space-y-2 text-sm">
                @foreach ($activity as $log)
                    <li class="border-b-2 border-dashed border-neutral-300 pb-2">
                        <p><strong>{{ $log->actor?->name ?? 'Sistem' }}</strong> · <span class="font-mono text-xs">{{ $log->action }}</span></p>
                        <p class="font-mono text-xs text-neutral-500">{{ $log->created_at?->diffForHumans() }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-senyum-card>

    <livewire:developer.territory-board :territoryId="$territory->id" />
</div>
@endsection
