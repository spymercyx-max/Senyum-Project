@extends('layouts.auth')

@section('title', 'Menunggu Persetujuan — SENYUM')

@section('content')
<p class="sn-kicker">Senyum Field Workspace</p>
<h1 class="sn-card-title mt-2">Welcome to Senyum</h1>
<p class="mt-1 text-sm">Halo, <strong>{{ auth()->user()->name }}</strong>! Pengajuan distributor Anda sedang kami tinjau.</p>

<div class="mt-4">
    <x-senyum-badge status="pending">Status: Menunggu Review</x-senyum-badge>
</div>

<ol class="mt-5 space-y-3 text-sm">
    <li class="sn-card sn-card-paper !p-3"><strong>01 — Pengajuan diterima.</strong><br><span class="text-neutral-600">Data Anda sudah masuk antrean.</span></li>
    <li class="sn-card sn-card-paper !p-3"><strong>02 — Tunggu review developer.</strong><br><span class="text-neutral-600">Biasanya 1–2 hari kerja. Anda akan diberi tahu.</span></li>
    <li class="sn-card sn-card-paper !p-3"><strong>03 — Konsultasi bila perlu.</strong><br><span class="text-neutral-600">Ada yang ingin ditanyakan? Hubungi kami lewat WhatsApp.</span></li>
</ol>

<div class="mt-5 grid gap-2">
    <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="sn-btn sn-btn-yellow sn-btn-block">Konsultasi via WhatsApp</a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="sn-btn sn-btn-ghost sn-btn-block">Keluar</button>
    </form>
</div>

<div class="mt-4">
    <x-senyum-alert type="action" title="Langkah berikutnya">Tunggu kabar dari kami. Sambil menunggu, siapkan daftar outlet yang ingin Anda ajak kerja sama.</x-senyum-alert>
</div>
@endsection
