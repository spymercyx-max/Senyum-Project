@extends('layouts.distributor')

@section('title', 'Pemesanan Cepat — Field Workspace')

@section('content')
<p class="sn-kicker">Sales &middot; Pemesanan</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Pesan untuk Outlet</h1>
<p class="mt-1 text-sm text-neutral-600">Pilih outlet, tambah produk, cek total, lalu kirim. Stok otomatis berkurang.</p>

<div class="mt-5 max-w-5xl">
    <div class="sn-card sn-card-cyan !p-4 text-sm mb-4">
        <strong>Wilayah:</strong> {{ $territory ?? '—' }} &middot; Harga default per baris memakai harga distributor.
        Lihat <a class="underline font-bold" href="{{ route('distributor.transaksi') }}">riwayat transaksi</a>.
    </div>
    @livewire('distributor.order-form')
</div>

<div class="mt-6">
    @livewire('distributor.stock-board')
</div>

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">Setelah pesanan tercatat, cek <a class="underline font-bold" href="{{ route('distributor.transaksi') }}">riwayat transaksi</a> atau minta restok lewat <a class="underline font-bold" href="{{ route('distributor.po') }}">PO</a> bila stok menipis.</x-senyum-alert>
</div>
@endsection
