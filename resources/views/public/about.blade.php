@extends('layouts.public')

@section('title', 'Tentang SENYUM — Kretek Tangan Asli')
@section('meta_description', 'Cerita SENYUM: kretek tangan dari tembakau pilihan, cengkeh asli, dan 13+ rempah fermentasi. Kenali komposisi, karakter, dan batasan kami.')
@section('og_title', 'Tentang SENYUM — Kretek Tangan Asli')
@section('og_description', 'Dilinting dengan tangan, dicatat dengan rapi. Kenali SENYUM lebih dekat.')

@section('content')
<div class="sn-hero-grid border-b-[3px] border-[#141414]">
    <div class="max-w-7xl mx-auto px-4 py-10 sm:py-12">
        <p class="sn-kicker">Tentang Senyum</p>
        <h1 class="font-display uppercase text-4xl sm:text-5xl mt-4 leading-tight">Dilinting tangan,<br>dicatat rapi.</h1>
        <p class="mt-4 max-w-2xl leading-relaxed">
            SENYUM lahir dari keyakinan sederhana: kretek yang baik dibuat perlahan.
            Tembakau pilihan, cengkeh asli, dan saus tembakau dari fermentasi rempah
            dilinting satu per satu oleh tangan-tangan terlatih.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4">
    <x-senyum-section number="01" title="Quote Kami" desc="Satu kalimat yang menjelaskan semuanya.">
        <blockquote class="sn-card sn-card-yellow font-display uppercase text-lg sm:text-xl leading-relaxed">
            "Kretek tangan SENYUM: tembakau pilihan, cengkeh asli, dan 13+ rempah
            yang difermentasi menjadi saus tembakau — dilinting satu per satu."
        </blockquote>
    </x-senyum-section>

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
    </x-senyum-section>
</div>

<div class="bg-white border-y-[3px] border-[#141414]">
    <div class="max-w-7xl mx-auto px-4">
        <x-senyum-section number="03" title="Karakter" desc="Faktual soal rasa, aroma, dan karakter — tanpa janji muluk.">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="sn-card"><h3 class="sn-card-title">Rasa</h3><p class="text-sm leading-relaxed">Hangat rempah, gurih tembakau, manis tipis cengkeh. Tarikan tegas dengan aftertaste kering.</p></div>
                <div class="sn-card"><h3 class="sn-card-title">Aroma</h3><p class="text-sm leading-relaxed">Cengkeh dan kayu manis dominan, disusul pandan, pala, dan bunga lawang yang samar.</p></div>
                <div class="sn-card"><h3 class="sn-card-title">Bakar</h3><p class="text-sm leading-relaxed">Lintingan tangan yang padat membuat bara merata dan tidak mudah mati.</p></div>
            </div>
        </x-senyum-section>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4">
    <div class="my-10">
        <x-senyum-alert type="danger" title="Khusus 18+">
            Produk ini mengandung tembakau dan nikotin. Merokok membahayakan kesehatan dan dapat menimbulkan kecanduan.
            Karakter rasa yang khas bukan berarti sehat, aman, atau bersifat obat. Jauhkan dari anak-anak.
        </x-senyum-alert>
    </div>

    <div class="my-10 flex flex-col sm:flex-row gap-3">
        <a href="{{ route('products.index') }}" class="sn-btn sn-btn-yellow w-full sm:w-auto">Lihat Produk</a>
        <a href="{{ route('partnership') }}" class="sn-btn sn-btn-ghost w-full sm:w-auto">Kemitraan</a>
    </div>
</div>
@endsection
