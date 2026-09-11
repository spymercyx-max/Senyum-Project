@extends('layouts.developer')

@section('title', 'Purchase Order — SENYUM Command Center')

@section('content')
<div class="space-y-6">
    <div>
        <p class="sn-kicker">Operations · Purchase Order</p>
        <h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Purchase Order</h1>
        <p class="text-sm text-neutral-600 mt-1">Tinjau dan proses pesanan dari distributor.</p>
    </div>

    @if (session('success'))
        <x-senyum-alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-senyum-alert type="danger" :message="session('error')" />
    @endif

    <div class="flex flex-wrap gap-2" aria-label="Filter status PO">
        <a href="{{ route('developer.po', ['fulfillment' => $fulfillment]) }}" class="sn-btn sn-btn-sm {{ ! $status ? 'sn-btn-primary' : 'sn-btn-ghost' }}">SEMUA ({{ $counts['all'] ?? 0 }})</a>
        @foreach (['draft' => 'DRAF', 'disetujui' => 'DISETUJUI', 'ditolak' => 'DITOLAK', 'dikirim' => 'DIKIRIM', 'diambil' => 'DIAMBIL', 'selesai' => 'SELESAI'] as $value => $label)
            <a href="{{ route('developer.po', ['status' => $value, 'fulfillment' => $fulfillment]) }}" class="sn-btn sn-btn-sm {{ $status === $value ? 'sn-btn-primary' : 'sn-btn-ghost' }}">{{ $label }} ({{ $counts[$value] ?? 0 }})</a>
        @endforeach
    </div>

    <div class="flex flex-wrap gap-2" aria-label="Filter pemenuhan">
        <a href="{{ route('developer.po', ['status' => $status]) }}" class="sn-btn sn-btn-sm {{ ! $fulfillment ? 'sn-btn-tosca' : 'sn-btn-ghost' }}">SEMUA MODE</a>
        <a href="{{ route('developer.po', ['status' => $status, 'fulfillment' => 'delivery']) }}" class="sn-btn sn-btn-sm {{ $fulfillment === 'delivery' ? 'sn-btn-tosca' : 'sn-btn-ghost' }}">DIKIRIM</a>
        <a href="{{ route('developer.po', ['status' => $status, 'fulfillment' => 'pickup']) }}" class="sn-btn sn-btn-sm {{ $fulfillment === 'pickup' ? 'sn-btn-tosca' : 'sn-btn-ghost' }}">DIAMBIL</a>
    </div>

    @if ($orders->isEmpty())
        <x-senyum-empty-state title="Belum ada PO" message="Purchase order akan muncul di sini." />
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
                        <th>Status</th>
                        <th>Tgl Pesan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $po)
                        <tr>
                            <td><a href="{{ route('developer.po.show', $po) }}" class="font-mono font-bold underline">{{ $po->code }}</a></td>
                            <td>{{ $po->distributor?->name ?? '—' }}</td>
                            <td><span class="font-mono text-xs">{{ strtoupper($po->fulfillment ?? 'delivery') }}</span></td>
                            <td class="sn-num">{{ $po->items->count() }}</td>
                            <td class="sn-num">Rp{{ number_format($po->total_amount, 0, ',', '.') }}</td>
                            <td><span class="sn-badge sn-badge--{{ $po->status }}">{{ strtoupper($po->status) }}</span></td>
                            <td class="font-mono text-xs">{{ $po->order_date?->translatedFormat('d M Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="space-y-3 md:hidden">
            @foreach ($orders as $po)
                <div class="sn-card">
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('developer.po.show', $po) }}" class="font-mono font-bold text-sm underline">{{ $po->code }}</a>
                        <span class="sn-badge sn-badge--{{ $po->status }}">{{ strtoupper($po->status) }}</span>
                    </div>
                    <p class="text-xs mt-1">{{ $po->distributor?->name ?? '—' }} · {{ strtoupper($po->fulfillment ?? '') }} · {{ $po->items->count() }} item · Rp{{ number_format($po->total_amount, 0, ',', '.') }}</p>
                    <p class="text-xs text-neutral-500 mt-0.5">Tgl pesan: {{ $po->order_date?->translatedFormat('d M Y') ?? '—' }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
