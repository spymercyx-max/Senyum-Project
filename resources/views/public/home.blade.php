@extends('layouts.public')

@section('title', 'SENYUM — Kretek Tangan Asli')
@section('meta_description', 'Kretek tangan SENYUM: racikan tembakau pilihan, cengkeh asli, dan 13+ rempah fermentasi. Lihat produk, cara memesan, dan kemitraan distributor.')
@section('og_title', 'SENYUM — Kretek Tangan Asli')
@section('og_description', 'Racikan berani, rasa jujur. Kretek tangan dengan 13+ rempah fermentasi.')

@section('content')
<div class="sn-hero-grid border-b-[3px] border-[#141414]">
    <div class="max-w-7xl mx-auto px-4 py-12 lg:py-16 grid gap-10 lg:grid-cols-2 items-center">
        {{-- Kiri: headline --}}
        <div>
            <p class="sn-kicker">Kretek Tangan Asli</p>
            <h1 class="font-display uppercase leading-[1.02] mt-4 text-4xl sm:text-5xl" aria-label="Senyum — Kretek Tangan Asli">
                Senyum
            </h1>
            <p class="font-display uppercase text-xl sm:text-2xl mt-4">Racikan berani, rasa jujur.</p>
            <p class="mt-4 max-w-xl leading-relaxed">
                SENYUM adalah kretek tangan yang dilinting satu per satu dari tembakau pilihan,
                cengkeh asli, dan saus tembakau hasil fermentasi 13+ macam rempah.
                Diproduksi dalam batch terbatas dengan pencatatan stok yang rapi.
            </p>
            <div class="mt-6 flex flex-col sm:flex-row sm:flex-wrap gap-3">
                <a href="{{ route('products.index') }}" class="sn-btn sn-btn-yellow w-full sm:w-auto">Lihat Produk</a>
                <a href="{{ route('partnership') }}" class="sn-btn sn-btn-ghost w-full sm:w-auto">Konsultasi Kemitraan</a>
            </div>
            <p class="mt-4 font-mono text-[11px] uppercase tracking-widest text-neutral-500">
                Khusus 18+ — Merokok membahayakan kesehatan.
            </p>
        </div>

        {{-- Kanan: featured --}}
        <div>
            @if ($featured)
                <x-senyum-product-emblem
                    kicker="Produk Unggulan"
                    :title="$featured->name"
                    :price="'Rp' . number_format((int) $featured->price, 0, ',', '.')"
                >
                    @if ($featured->short_description)
                        <p>{{ $featured->short_description }}</p>
                    @else
                        <p>Racikan andalan SENYUM — ketersediaan mengikuti stok gudang.</p>
                    @endif
                    <p class="mt-2 font-mono text-xs uppercase tracking-widest">
                        Stok tersedia: {{ $featured->inventory?->available ?? 0 }}
                    </p>
                    <a href="{{ route('products.show', $featured->slug) }}" class="sn-btn sn-btn-cyan sn-btn-sm mt-4">
                        Lihat Detail
                    </a>
                </x-senyum-product-emblem>
            @else
                <div class="sn-pack-frame" role="status">
                    <div class="sn-pack-frame-inner">
                        <p class="sn-kicker">Featured Product</p>
                        <h2 class="font-display uppercase text-2xl mt-2">Belum ada produk unggulan yang tersedia.</h2>
                        <p class="mt-2 text-sm text-neutral-600">Katalog tetap bisa dijelajahi — stok diperbarui mengikuti gudang.</p>
                        <a href="{{ route('products.index') }}" class="sn-btn sn-btn-yellow sn-btn-sm mt-4">Lihat Produk</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="bg-white border-b-[3px] border-[#141414]">
    <div class="max-w-7xl mx-auto px-4">
    {{-- 01 TENTANG --}}
    <x-senyum-section number="01" title="Tentang Senyum" desc="Kretek tangan, bukan kretek mesin.">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="sn-card">
                <p class="leading-relaxed">
                    Setiap batang SENYUM dilinting dengan tangan — proses yang lambat, teliti,
                    dan tidak bisa dikebut mesin. Tembakau dipilih, dicampur cengkeh asli,
                    lalu disaus dengan hasil fermentasi rempah sebelum dilinting.
                </p>
                <blockquote class="mt-4 border-l-8 border-[#0BBCD6] bg-[#FFF9EC] border-2 border-black p-4 font-display uppercase text-sm leading-relaxed">
                    "Kretek tangan SENYUM: tembakau pilihan, cengkeh asli, dan 13+ rempah
                    yang difermentasi menjadi saus tembakau — dilinting satu per satu."
                </blockquote>
            </div>
            <div class="sn-card sn-card-yellow">
                <h3 class="sn-card-title">Kenapa tangan?</h3>
                <ul class="list-disc pl-5 space-y-2 text-sm leading-relaxed">
                    <li>Kepadatan lintingan lebih konsisten di tangan pelinting berpengalaman.</li>
                    <li>Campuran tembakau, cengkeh, dan saus rempah tercampur merata.</li>
                    <li>Setiap batch dicatat: bahan, tanggal produksi, dan stok.</li>
                </ul>
            </div>
        </div>
    </x-senyum-section>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4">
    {{-- 02 KOMPOSISI --}}
    <x-senyum-section number="02" title="Komposisi" desc="Mengandung 13+ macam rempah yang difermentasi menjadi saus tembakau.">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @php
                $bahan = ['Kencur', 'Kapulaga', 'Jinten Hitam', 'Keningar', 'Kayu Manis', 'Jadam', 'Cengkeh', 'Ketumbar', 'Daun Salam', 'Daun Pandan', 'Daun Sirih', 'Pala', 'Bunga Lawang', 'dan herbal lainnya'];
            @endphp
            @foreach ($bahan as $i => $nama)
                <div class="sn-card !p-4 flex gap-3 items-start">
                    <span class="font-display text-lg bg-black text-[#FFD21F] px-2 py-1 rounded" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="font-bold text-sm uppercase tracking-wide">{{ $nama }}</span>
                </div>
            @endforeach
        </div>
        <p class="mt-4 text-sm text-neutral-600">Komposisi dapat berbeda tipis antar varian. Lihat halaman detail tiap produk untuk catatan ketersediaan.</p>
    </x-senyum-section>
</div>

<div class="bg-white border-y-[3px] border-[#141414]">
    <div class="max-w-7xl mx-auto px-4">
    {{-- 03 KARAKTER --}}
    <x-senyum-section number="03" title="Karakter Senyum" desc="Apa yang terasa — tanpa klaim berlebihan.">
        <div class="grid gap-6 md:grid-cols-3">
            <div class="sn-card">
                <h3 class="sn-card-title">Rasa</h3>
                <p class="text-sm leading-relaxed">Hangat rempah dengan gurih tembakau dan manis tipis dari cengkeh. Tarikan tegas, aftertaste kering yang bersih.</p>
            </div>
            <div class="sn-card">
                <h3 class="sn-card-title">Aroma</h3>
                <p class="text-sm leading-relaxed">Aroma cengkeh dan kayu manis terasa sejak bungkus dibuka, disusul wangi pandan dan pala saat dibakar.</p>
            </div>
            <div class="sn-card">
                <h3 class="sn-card-title">Karakter</h3>
                <p class="text-sm leading-relaxed">Lintingan tangan yang padat, bakar merata, dan racikan yang konsisten antar batch. Cocok untuk perokok kretek berpengalaman.</p>
            </div>
        </div>
        <div class="mt-6">
            <x-senyum-alert type="warning" title="Catatan jujur">
                Kretek tetap produk tembakau dan merokok membahayakan kesehatan — karakter rasa yang khas bukan berarti sehat, aman, atau bersifat obat. Khusus 18 tahun ke atas.
            </x-senyum-alert>
        </div>
    </x-senyum-section>
    </div>
</div>

    {{-- PRODUK — blok aksen kuning tunggal (teks hitam, tanpa kuning-di-atas-kuning) --}}
<div class="bg-[#FFD21F] border-b-[3px] border-[#141414] [&_.sn-section-desc]:!text-black">
    <div class="max-w-7xl mx-auto px-4">
    <x-senyum-section number="04" title="Produk Pilihan" desc="Maksimal tiga varian andalan minggu ini.">
        @if ($products->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($products as $product)
                    <x-senyum-product-card :product="$product" />
                @endforeach
            </div>
            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('products.index') }}" class="sn-btn sn-btn-primary w-full sm:w-auto">Lihat Semua Produk</a>
            </div>
        @else
            <x-senyum-empty-state title="Belum ada produk" message="Katalog sedang disiapkan. Silakan kembali lagi." action="Ke Beranda" href="{{ url('/') }}" />
        @endif
    </x-senyum-section>
    </div>
</div>

<div class="bg-white border-b-[3px] border-[#141414]">
    <div class="max-w-7xl mx-auto px-4">
    {{-- CARA MEMESAN --}}
    <x-senyum-section number="05" title="Cara Memesan" desc="Empat langkah sederhana lewat WhatsApp resmi.">
        <ol class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 list-none p-0">
            <li class="sn-card"><p class="font-display text-2xl" aria-hidden="true">01</p><h3 class="sn-card-title !text-base mt-2">Pilih Produk</h3><p class="text-sm">Buka katalog, baca detail varian, catat nama produk dan jumlah yang diinginkan.</p></li>
            <li class="sn-card"><p class="font-display text-2xl" aria-hidden="true">02</p><h3 class="sn-card-title !text-base mt-2">Hubungi Admin</h3><p class="text-sm">Klik tombol pesan — WhatsApp terbuka dengan format pesan yang sudah terisi.</p></li>
            <li class="sn-card"><p class="font-display text-2xl" aria-hidden="true">03</p><h3 class="sn-card-title !text-base mt-2">Konfirmasi</h3><p class="text-sm">Admin memeriksa ketersediaan stok lalu menginformasikan total dan pembayaran.</p></li>
            <li class="sn-card"><p class="font-display text-2xl" aria-hidden="true">04</p><h3 class="sn-card-title !text-base mt-2">Kirim / Ambil</h3><p class="text-sm">Pesanan dikirim atau diambil sesuai kesepakatan. Simpan bukti pembayaran.</p></li>
        </ol>
        <p class="mt-4 text-sm text-neutral-600">Nomor pemesanan resmi: <span class="font-mono font-bold">{{ $waOrder }}</span>. Hati-hati terhadap nomor selain yang tercantum di situs ini.</p>
    </x-senyum-section>
    </div>
</div>

    {{-- KEMITRAAN TEASER — section struktural hitam --}}
<div class="bg-[#141414] text-white [&_.sn-section-title]:!text-white [&_.sn-section-desc]:!text-neutral-300 [&_.sn-section-number]:!border-[#FFD21F]">
    <div class="max-w-7xl mx-auto px-4">
    <x-senyum-section number="06" title="Kemitraan Senyum" desc="Kelola wilayah, outlet, dan pasokan dengan sistem yang rapi.">
        <div class="sn-card sn-card-black flex flex-col md:flex-row md:items-center gap-6 !border-[#FFD21F]">
            <div class="flex-1">
                <p class="sn-kicker sn-kicker--on-dark">Distributor Wilayah</p>
                <p class="mt-2 leading-relaxed text-white">Distributor adalah pihak kerja sama distribusi untuk wilayah yang disepakati — dengan harga distributor sesuai ketentuan dan workspace untuk mencatat transaksi, outlet, dan kunjungan.</p>
            </div>
            <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3">
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', (string) $waDev) }}" target="_blank" rel="noopener" class="sn-btn sn-btn-yellow w-full sm:w-auto">Konsultasi Kemitraan</a>
                <a href="{{ route('partnership') }}" class="sn-btn sn-btn-ghost w-full sm:w-auto">Pelajari Dulu</a>
            </div>
        </div>
    </x-senyum-section>
    </div>
</div>
@endsection
