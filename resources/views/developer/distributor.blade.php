@extends('layouts.developer')

@section('title', 'Distributor — SENYUM Command Center')

@section('content')
<div class="space-y-6">
    <div>
        <p class="sn-kicker">Network · Distributor</p>
        <h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Distributor</h1>
        <p class="text-sm text-neutral-600 mt-1">Tinjau pengajuan, setujui, tolak, atau tangguhkan distributor.</p>
    </div>

    @if (session('success'))
        <x-senyum-alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-senyum-alert type="danger" :message="session('error')" />
    @endif

    <div class="flex flex-wrap gap-2" role="tablist" aria-label="Filter status distributor">
        @foreach (['pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'suspended' => 'Ditangguhkan'] as $value => $label)
            <a href="{{ route('developer.distributor', ['status' => $value]) }}" role="tab" aria-selected="{{ $status === $value ? 'true' : 'false' }}"
               class="sn-btn sn-btn-sm {{ $status === $value ? 'sn-btn-primary' : 'sn-btn-ghost' }}">{{ strtoupper($label) }} ({{ $counts[$value] ?? 0 }})</a>
        @endforeach
    </div>

    @if ($distributors->isEmpty())
        <x-senyum-empty-state title="Tidak ada distributor" message="Belum ada distributor dengan status {{ strtoupper($status) }}." />
    @else
        <div class="sn-table-wrap hidden md:block">
            <table class="sn-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>WhatsApp</th>
                        <th>Kota</th>
                        <th>Wilayah</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($distributors as $d)
                        <tr>
                            <td>
                                <a href="{{ route('developer.distributor.show', $d) }}" class="font-bold underline">{{ $d->name }}</a>
                                <p class="font-mono text-xs text-neutral-500">{{ '@' . $d->username }}</p>
                            </td>
                            <td class="font-mono text-xs">{{ $d->profile?->whatsapp ?? $d->phone ?? '—' }}</td>
                            <td>{{ $d->profile?->city ?? '—' }}<p class="text-xs text-neutral-500">{{ $d->profile?->district ?? '' }}</p></td>
                            <td>{{ $d->distributorProfile?->territory ? $d->distributorProfile->territory->city . ' – ' . $d->distributorProfile->territory->district : '—' }}</td>
                            <td class="font-mono text-xs">{{ $d->created_at?->translatedFormat('d M Y') }}</td>
                            <td><x-senyum-badge status="{{ $d->distributorStatus() ?? 'pending' }}">{{ strtoupper($d->distributorStatus() ?? 'PENDING') }}</x-senyum-badge></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="space-y-3 md:hidden">
            @foreach ($distributors as $d)
                <div class="sn-card">
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('developer.distributor.show', $d) }}" class="font-display font-bold uppercase text-sm underline">{{ $d->name }}</a>
                        <x-senyum-badge status="{{ $d->distributorStatus() ?? 'pending' }}">{{ strtoupper($d->distributorStatus() ?? 'PENDING') }}</x-senyum-badge>
                    </div>
                    <p class="font-mono text-xs text-neutral-500 mt-1">{{ '@' . $d->username }} · {{ $d->profile?->whatsapp ?? $d->phone ?? '—' }}</p>
                    <p class="text-xs mt-1">{{ $d->profile?->city ?? '—' }} · {{ $d->distributorProfile?->territory ? $d->distributorProfile->territory->city . ' – ' . $d->distributorProfile->territory->district : 'Tanpa wilayah' }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $distributors->links() }}</div>
    @endif
</div>
@endsection
