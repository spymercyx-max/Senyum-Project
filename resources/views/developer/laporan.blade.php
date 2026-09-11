@extends('layouts.developer')

@section('title', 'Laporan — SENYUM Command Center')

@section('content')
<div class="space-y-6">
    <div>
        <p class="sn-kicker">Analytics · Laporan</p>
        <h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Laporan</h1>
        <p class="text-sm text-neutral-600 mt-1">Penjualan produk, aktivitas wilayah, kinerja distributor & outlet, dan pergerakan stok.</p>
    </div>

    <x-senyum-card title="Penjualan per produk" kicker="Sales by product">
        @if ($salesPerProduct->isEmpty())
            <x-senyum-empty-state title="Belum ada penjualan" message="Data penjualan produk akan muncul di sini." />
        @else
            <div class="sn-table-wrap hidden md:block">
                <table class="sn-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Terjual</th>
                            <th>Baris transaksi</th>
                            <th>Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($salesPerProduct as $row)
                            <tr>
                                <td class="font-bold">{{ $row->product?->name ?? '—' }}</td>
                                <td class="sn-num">{{ $row->total_qty }} pcs</td>
                                <td class="sn-num">{{ $row->tx_lines }}</td>
                                <td class="sn-num">Rp{{ number_format($row->total_revenue, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="space-y-3 md:hidden">
                @foreach ($salesPerProduct as $row)
                    <div class="sn-card !p-3">
                        <p class="font-display font-bold uppercase text-sm">{{ $row->product?->name ?? '—' }}</p>
                        <p class="text-xs mt-1">{{ $row->total_qty }} pcs · Rp{{ number_format($row->total_revenue, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </x-senyum-card>

    <x-senyum-card title="Aktivitas wilayah" kicker="Territory activity">
        @if ($territoryActivity->isEmpty())
            <x-senyum-empty-state title="Belum ada data" message="Aktivitas per wilayah akan muncul di sini." />
        @else
            <div class="sn-table-wrap hidden md:block">
                <table class="sn-table">
                    <thead>
                        <tr>
                            <th>Wilayah</th>
                            <th>Outlet</th>
                            <th>Transaksi</th>
                            <th>Kunjungan</th>
                            <th>Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($territoryActivity as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('developer.wilayah.show', $row['territory']) }}" class="font-bold underline">{{ $row['territory']->city }} – {{ $row['territory']->district }}</a>
                                </td>
                                <td class="sn-num">{{ $row['outlets'] }}</td>
                                <td class="sn-num">{{ $row['transactions'] }}</td>
                                <td class="sn-num">{{ $row['visits'] }}</td>
                                <td class="sn-num">Rp{{ number_format($row['revenue'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="space-y-3 md:hidden">
                @foreach ($territoryActivity as $row)
                    <div class="sn-card !p-3">
                        <a href="{{ route('developer.wilayah.show', $row['territory']) }}" class="font-display font-bold uppercase text-sm underline">{{ $row['territory']->city }} – {{ $row['territory']->district }}</a>
                        <p class="text-xs mt-1">{{ $row['outlets'] }} outlet · {{ $row['transactions'] }} transaksi · {{ $row['visits'] }} kunjungan · Rp{{ number_format($row['revenue'], 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </x-senyum-card>

    <x-senyum-card title="Kinerja distributor" kicker="Distributor performance">
        @if ($distributorPerformance->isEmpty())
            <x-senyum-empty-state title="Belum ada data" message="Kinerja distributor akan muncul di sini." />
        @else
            <div class="sn-table-wrap hidden md:block">
                <table class="sn-table">
                    <thead>
                        <tr>
                            <th>Distributor</th>
                            <th>Wilayah</th>
                            <th>Transaksi</th>
                            <th>Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($distributorPerformance as $d)
                            <tr>
                                <td>
                                    <a href="{{ route('developer.distributor.show', $d) }}" class="font-bold underline">{{ $d->name }}</a>
                                    <p class="font-mono text-xs text-neutral-500">{{ '@' . $d->username }}</p>
                                </td>
                                <td>{{ $d->distributorProfile?->territory ? $d->distributorProfile->territory->city . ' – ' . $d->distributorProfile->territory->district : '—' }}</td>
                                <td class="sn-num">{{ $d->transactions_count }}</td>
                                <td class="sn-num">Rp{{ number_format($d->transactions_sum_total_amount ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="space-y-3 md:hidden">
                @foreach ($distributorPerformance as $d)
                    <div class="sn-card !p-3">
                        <a href="{{ route('developer.distributor.show', $d) }}" class="font-display font-bold uppercase text-sm underline">{{ $d->name }}</a>
                        <p class="text-xs mt-1">{{ $d->transactions_count }} transaksi · Rp{{ number_format($d->transactions_sum_total_amount ?? 0, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </x-senyum-card>

    <x-senyum-card title="Kinerja outlet" kicker="Outlet performance">
        @if ($outletPerformance->isEmpty())
            <x-senyum-empty-state title="Belum ada data" message="Kinerja outlet akan muncul di sini." />
        @else
            <div class="sn-table-wrap hidden md:block">
                <table class="sn-table">
                    <thead>
                        <tr>
                            <th>Outlet</th>
                            <th>Wilayah</th>
                            <th>Distributor</th>
                            <th>Transaksi</th>
                            <th>Omzet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($outletPerformance as $o)
                            <tr>
                                <td>
                                    <p class="font-bold">{{ $o->name }}</p>
                                    <p class="text-xs text-neutral-500">{{ $o->city }} · {{ $o->district }}</p>
                                </td>
                                <td>{{ $o->territory?->city ?? '—' }} – {{ $o->territory?->district ?? '' }}</td>
                                <td>{{ $o->distributor?->name ?? '—' }}</td>
                                <td class="sn-num">{{ $o->transactions_count }}</td>
                                <td class="sn-num">Rp{{ number_format($o->transactions_sum_total_amount ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="space-y-3 md:hidden">
                @foreach ($outletPerformance as $o)
                    <div class="sn-card !p-3">
                        <p class="font-display font-bold uppercase text-sm">{{ $o->name }}</p>
                        <p class="text-xs mt-1">{{ $o->transactions_count }} transaksi · Rp{{ number_format($o->transactions_sum_total_amount ?? 0, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </x-senyum-card>

    <x-senyum-card title="Pergerakan inventory" kicker="Inventory movement">
        @if ($inventoryMovement->isEmpty())
            <x-senyum-empty-state title="Belum ada pergerakan" message="Penyesuaian stok akan tercatat di sini." />
        @else
            <ul class="space-y-2 text-sm">
                @foreach ($inventoryMovement as $log)
                    <li class="border-b-2 border-dashed border-neutral-300 pb-2">
                        <p><strong>{{ $log->actor?->name ?? 'Sistem' }}</strong> · <span class="font-mono text-xs">{{ $log->action }}</span></p>
                        @if (! empty($log->metadata['product']))
                            <p class="text-xs">{{ $log->metadata['product'] }} ({{ ($log->metadata['delta'] ?? 0) > 0 ? '+' : '' }}{{ $log->metadata['delta'] ?? 0 }}){{ ! empty($log->metadata['reason']) ? ' · ' . $log->metadata['reason'] : '' }}</p>
                        @endif
                        <p class="font-mono text-xs text-neutral-500">{{ $log->created_at?->translatedFormat('d M Y H:i') }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-senyum-card>
</div>
@endsection
