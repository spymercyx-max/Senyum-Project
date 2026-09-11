@extends('layouts.public')

@section('title', 'Produk SENYUM — Katalog Kretek Tangan')
@section('meta_description', 'Katalog kretek tangan SENYUM: varian aktif, harga, dan ketersediaan stok. Cari produk favoritmu dan pesan via WhatsApp resmi.')
@section('og_title', 'Produk SENYUM — Katalog Kretek Tangan')
@section('og_description', 'Jelajahi varian kretek tangan SENYUM dan cek ketersediaannya.')

@section('content')
<div class="sn-hero-grid border-b-[3px] border-[#141414]">
    <div class="max-w-7xl mx-auto px-4 py-10 sm:py-12">
        <p class="sn-kicker">Katalog</p>
        <h1 class="font-display uppercase text-4xl sm:text-5xl mt-4 leading-tight">Produk Senyum</h1>
        <p class="mt-3 max-w-2xl text-neutral-600 leading-relaxed">
            Semua varian aktif yang tercatat di gudang. Ketersediaan mengikuti stok nyata —
            jika habis, tombol pesan tetap membuka WhatsApp untuk menanyakan restock.
        </p>
        <p class="mt-3 font-mono text-xs uppercase tracking-widest text-neutral-500">
            {{ $products->total() }} produk aktif
        </p>
    </div>
</div>

<div class="bg-white border-b-[3px] border-[#141414]">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <div>
            <livewire:catalog.product-filter :q="request('q', '')" />
        </div>

        <p class="mt-8 font-mono text-[11px] uppercase tracking-widest text-neutral-500">
            Khusus 18+ — Merokok membahayakan kesehatan.
        </p>
    </div>
</div>
@endsection
