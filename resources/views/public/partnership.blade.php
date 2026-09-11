@extends('layouts.public')

@section('title', 'Kemitraan SENYUM — Jadi Distributor Wilayah')
@section('meta_description', 'Kemitraan distributor Kretek Senyum: apa itu kemitraan, bagaimana caranya, apa yang tersedia, wilayah distribusi, dan konsultasi via WhatsApp.')
@section('og_title', 'Kemitraan SENYUM — Jadi Distributor Wilayah')
@section('og_description', 'Kelola wilayah, outlet, dan pasokan dengan sistem yang rapi.')

@section('content')
<div class="sn-hero-grid border-b-[3px] border-[#141414]">
    <div class="max-w-7xl mx-auto px-4 py-10 sm:py-12">
        <p class="sn-kicker">Kemitraan</p>
        <h1 class="font-display uppercase text-4xl sm:text-5xl mt-4 leading-tight">Kemitraan Senyum</h1>
        <p class="mt-3 max-w-2xl leading-relaxed">
            Program kerja sama distribusi untuk yang serius mengelola wilayah:
            mencatat outlet, merawat kunjungan, dan menjaga pasokan tetap jalan.
        </p>
    </div>
</div>

<div class="bg-white border-b-[3px] border-[#141414]">
    <div class="max-w-7xl mx-auto px-4">
        <x-senyum-section number="01" title="Apa Itu Kemitraan?" desc="Peran distributor dalam ekosistem SENYUM.">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="sn-card">
                    <p class="leading-relaxed">
                        Distributor adalah pihak kerja sama distribusi untuk wilayah yang disepakati bersama.
                        Kamu memasarkan dan menyalurkan produk SENYUM ke outlet di wilayahmu dengan
                        <span class="font-bold">harga distributor sesuai ketentuan</span> yang ditetapkan.
                    </p>
                </div>
                <div class="sn-card sn-card-cyan">
                    <h3 class="sn-card-title">Yang kamu kelola</h3>
                    <ul class="list-disc pl-5 space-y-2 text-sm leading-relaxed">
                        <li>Wilayah distribusi yang disepakati.</li>
                        <li>Outlet: pendataan, pembinaan, dan pencatatan transaksi.</li>
                        <li>Transaksi penjualan dan pemesanan ulang (purchase order).</li>
                        <li>Inventory: pantau stok dan ambang batas.</li>
                        <li>Visit: kunjungan rutin dan monitoring outlet.</li>
                    </ul>
                </div>
            </div>
        </x-senyum-section>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4">
    <x-senyum-section number="02" title="Bagaimana Caranya?" desc="Tujuh langkah dari membaca hingga workspace terbuka.">
        <ol class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 list-none p-0">
            @foreach ($steps as $step)
                <li class="sn-card">
                    <p class="font-display text-2xl" aria-hidden="true">{{ $step['no'] }}</p>
                    <h3 class="sn-card-title !text-base mt-2">{{ $step['title'] }}</h3>
                    <p class="text-sm leading-relaxed">{{ $step['desc'] }}</p>
                </li>
            @endforeach
        </ol>
        <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <a href="{{ route('register') }}" class="sn-btn sn-btn-yellow w-full sm:w-auto">Daftar Sebagai Distributor</a>
            <a href="{{ route('login') }}" class="sn-btn sn-btn-ghost w-full sm:w-auto">Sudah Daftar? Masuk</a>
        </div>
    </x-senyum-section>
</div>

<div class="bg-white border-y-[3px] border-[#141414]">
    <div class="max-w-7xl mx-auto px-4">
        <x-senyum-section number="03" title="Apa Yang Tersedia?" desc="Fasilitas kerja, bukan janji manis.">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="sn-card">
                    <h3 class="sn-card-title">Workspace Distributor</h3>
                    <ul class="list-disc pl-5 space-y-2 text-sm leading-relaxed">
                        <li>Dashboard pemesanan dan riwayat transaksi.</li>
                        <li>Pencatatan outlet, kunjungan, dan peta sebaran.</li>
                        <li>Pengajuan purchase order dan pantauan statusnya.</li>
                        <li>Profil, notifikasi, dan pusat bantuan.</li>
                    </ul>
                </div>
                <div class="sn-card">
                    <h3 class="sn-card-title">Ketentuan Dagang</h3>
                    <ul class="list-disc pl-5 space-y-2 text-sm leading-relaxed">
                        <li>Harga distributor mengikuti ketentuan yang berlaku.</li>
                        <li>Wilayah dan outlet tercatat agar tidak tumpang tindih.</li>
                        <li>Evaluasi berkala berdasarkan aktivitas yang tercatat.</li>
                    </ul>
                </div>
            </div>
            <div class="mt-6">
                <x-senyum-alert type="warning" title="Tanpa janji muluk">
                    Kami tidak menjanjikan keuntungan pasti, penghasilan tetap, atau ROI tertentu.
                    Hasil kemitraan bergantung pada kerja lapangan, kondisi pasar, dan ketersediaan stok.
                </x-senyum-alert>
            </div>
        </x-senyum-section>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4">
    <x-senyum-section number="04" title="Wilayah" desc="Distribusi berbasis wilayah yang disepakati.">
        <div class="sn-card flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="sn-stat flex-1">
                <p class="sn-stat-label">Wilayah terdata</p>
                <p class="sn-stat-value">{{ number_format($territories, 0, ',', '.') }}</p>
                <p class="sn-stat-sub">Kota &amp; kecamatan yang sudah tercatat di sistem.</p>
            </div>
            <p class="text-sm leading-relaxed flex-1">
                Saat mendaftar kamu mengisi kota dan kecamatan. Tim kami meninjau
                ketersediaan wilayah tersebut sebelum menyetujui akunmu — satu wilayah
                dikelola agar pembinaan outlet fokus dan merata.
            </p>
        </div>
    </x-senyum-section>
</div>

<div class="bg-[#141414] text-white [&_.sn-section-title]:!text-white [&_.sn-section-desc]:!text-neutral-300 [&_.sn-section-number]:!border-[#FFD21F]">
    <div class="max-w-7xl mx-auto px-4">
        <x-senyum-section number="05" title="Konsultasi" desc="Tanya dulu sebelum daftar — gratis, tanpa komitmen.">
            <div class="sn-card sn-card-black flex flex-col md:flex-row md:items-center gap-6 !border-[#FFD21F]">
                <p class="flex-1 leading-relaxed text-white">
                    Ceritakan kotamu, pengalaman jualanmu, dan rencana outlet yang akan kamu garap.
                    Admin akan menjelaskan ketentuan yang berlaku saat ini.
                </p>
                <a href="{{ $waConsult }}" target="_blank" rel="noopener" class="sn-btn sn-btn-yellow sn-btn-lg w-full md:w-auto shrink-0">Konsultasi Kemitraan</a>
            </div>
            <p class="mt-3 text-sm text-neutral-300">Catatan: pesan dibalas pada jam kerja. Kami tidak menjanjikan respon instan.</p>
        </x-senyum-section>
    </div>
</div>
@endsection
