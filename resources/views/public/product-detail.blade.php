@extends('layouts.public')

@section('title', $product->name . ' — SENYUM')
@section('meta_description', $product->short_description ?? ('Detail ' . $product->name . ': harga, ketersediaan, dan cara pemesanan via WhatsApp resmi SENYUM.'))
@section('og_title', $product->name . ' — SENYUM')
@section('og_description', $product->short_description ?? 'Kretek tangan SENYUM.')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
    <nav aria-label="Breadcrumb" class="font-mono text-xs uppercase tracking-widest">
        <ol class="flex flex-wrap gap-2 items-center">
            <li><a href="{{ url('/') }}" class="underline underline-offset-4">Home</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('products.index') }}" class="underline underline-offset-4">Produk</a></li>
            <li aria-hidden="true">/</li>
            <li aria-current="page" class="font-bold">{{ $product->name }}</li>
        </ol>
    </nav>

    @php
        $available = ($product->inventory?->available ?? 0) > 0;
    @endphp

    <div class="mt-8 grid gap-8 lg:gap-10 lg:grid-cols-2">
        <div class="sn-pack-frame">
            <span class="sn-pack-corner" aria-hidden="true"><x-senyum-logo size="sm" /></span>
            <div class="sn-pack-frame-inner text-center">
                @if ($product->image)
                    <img src="{{ $product->image }}" alt="Kemasan {{ $product->name }}" class="mx-auto max-h-72 w-full aspect-[4/3] object-contain">
                @else
                    <x-senyum-logo size="lg" />
                    <p class="font-mono text-xs font-bold uppercase tracking-widest mt-2 text-[#062A30]">Kemasan Senyum</p>
                @endif
            </div>
        </div>

        <div>
            <p class="sn-kicker">Detail Produk</p>
            <h1 class="font-display uppercase text-3xl sm:text-4xl mt-3">{{ $product->name }}</h1>
            <p class="mt-2 font-mono text-xs uppercase tracking-widest text-neutral-500">SKU: {{ $product->sku }}</p>
            <p class="mt-3 font-mono font-bold text-2xl">Rp{{ number_format((int) $product->price, 0, ',', '.') }}</p>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <x-senyum-badge :status="$available ? 'active' : 'suspended'">{{ $available ? 'Tersedia' : 'Habis' }}</x-senyum-badge>
                <span class="font-mono text-xs uppercase tracking-widest text-neutral-500">
                    Stok tersedia: {{ $product->inventory?->available ?? 0 }}
                </span>
            </div>

            @if ($product->short_description)
                <p class="mt-4 leading-relaxed">{{ $product->short_description }}</p>
            @endif

            @php
                $customerTiers = $product->relationLoaded('tiers')
                    ? $product->tiers->where('channel', 'customer')->sortBy('min_qty')->values()
                    : $product->tiers()->where('channel', 'customer')->orderBy('min_qty')->get();
            @endphp

            @if ($customerTiers->count() > 0)
                <section class="mt-6" aria-label="Harga bertingkat customer">
                    <h2 class="sn-card-title !text-base">Harga Bertingkat</h2>
                    <div class="sn-table-wrap mt-3">
                        <table class="sn-table">
                            <caption class="sr-only">Harga customer berdasarkan jumlah pembelian {{ $product->name }}</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Jumlah</th>
                                    <th scope="col" class="sn-num">Harga satuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customerTiers as $tier)
                                    <tr>
                                        <td class="font-mono font-bold">
                                            @if (is_null($tier->max_qty))
                                                {{ $tier->min_qty }}+
                                            @else
                                                {{ $tier->min_qty }}–{{ $tier->max_qty }}
                                            @endif
                                        </td>
                                        <td class="sn-num">Rp{{ number_format((int) $tier->price, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="sn-table-note">Harga customer per jumlah pembelian dalam satu transaksi. Konfirmasi total akhir via WhatsApp.</p>
                </section>
            @endif

            @if ($product->description)
                <div class="mt-4 text-sm leading-relaxed text-neutral-700">
                    {!! nl2br(e($product->description)) !!}
                </div>
            @endif

            @if ($product->ingredients)
                <div class="mt-6 sn-card !p-4">
                    <h2 class="sn-card-title !text-base">Komposisi / Bahan</h2>
                    <p class="text-sm leading-relaxed">{{ $product->ingredients }}</p>
                </div>
            @endif

            @if ($product->availability_note)
                <p class="mt-4 text-sm text-neutral-600"><span class="font-bold">Catatan ketersediaan:</span> {{ $product->availability_note }}</p>
            @endif

            <div class="mt-6 flex flex-col sm:flex-row sm:flex-wrap gap-3">
                <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="sn-btn sn-btn-yellow sn-btn-lg w-full sm:w-auto">Pesan Melalui WhatsApp</a>
                <a href="{{ route('products.index') }}" class="sn-btn sn-btn-ghost w-full sm:w-auto">Kembali ke Katalog</a>
            </div>
            <p class="mt-3 text-sm text-neutral-500">Membuka WhatsApp bukan berarti order otomatis dikonfirmasi.</p>
            <p class="mt-2 font-mono text-[11px] uppercase tracking-widest text-neutral-500">Khusus 18+ — Merokok membahayakan kesehatan.</p>
        </div>
    </div>

    @if ($related->count() > 0)
        <x-senyum-section number="R" title="Produk Terkait" desc="Varian aktif lain yang mungkin cocok.">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($related as $item)
                    <x-senyum-product-card :product="$item" />
                @endforeach
            </div>
        </x-senyum-section>
    @endif
</div>
@endsection
