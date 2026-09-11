@extends('layouts.developer')

@section('title', 'Produk — SENYUM Command Center')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end gap-3">
        <div>
            <p class="sn-kicker">Product · Katalog</p>
            <h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Produk</h1>
            <p class="text-sm text-neutral-600 mt-1">Kelola katalog, harga customer & distributor, tier, dan produk unggulan.</p>
        </div>
        <a href="{{ route('developer.produk.create') }}" class="sn-btn sn-btn-tosca sn-btn-sm ml-auto">+ Tambah produk</a>
    </div>

    @if (session('success'))
        <x-senyum-alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-senyum-alert type="danger" :message="session('error')" />
    @endif

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        <x-senyum-stat label="Total produk" :value="$stats['total']" />
        <x-senyum-stat label="Aktif" :value="$stats['active']" />
        <x-senyum-stat label="Draf" :value="$stats['draft']" />
        <x-senyum-stat label="Diarsipkan" :value="$stats['archived']" />
    </div>

    <div class="flex flex-wrap gap-2" aria-label="Filter status produk">
        <a href="{{ route('developer.produk') }}" class="sn-btn sn-btn-sm {{ ! $status ? 'sn-btn-primary' : 'sn-btn-ghost' }}">SEMUA</a>
        @foreach (['draft' => 'DRAF', 'active' => 'AKTIF', 'archived' => 'DIARSIPKAN'] as $value => $label)
            <a href="{{ route('developer.produk', ['status' => $value, 'q' => $q]) }}" class="sn-btn sn-btn-sm {{ $status === $value ? 'sn-btn-primary' : 'sn-btn-ghost' }}">{{ $label }}</a>
        @endforeach
    </div>

    @if ($stats['featured'])
        <x-senyum-alert type="info" title="Produk unggulan">
            {{ $stats['featured']->name }} ({{ $stats['featured']->sku }}) sedang tampil sebagai produk unggulan.
        </x-senyum-alert>
    @endif

    <x-senyum-card title="Kelola produk" kicker="Product manager">
        <livewire:developer.product-manager :search="$q" :status="$status ?? ''" />
    </x-senyum-card>
</div>
@endsection
