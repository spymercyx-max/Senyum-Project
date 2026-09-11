@extends('layouts.auth')

@section('title', 'Akun Ditangguhkan — SENYUM')

@section('content')
<p class="sn-kicker">Senyum Field Workspace</p>
<h1 class="sn-card-title mt-2">Akun Ditangguhkan</h1>
<p class="mt-1 text-sm">Halo, <strong>{{ auth()->user()->name }}</strong>. Akun distributor Anda sedang ditangguhkan sementara.</p>

<div class="mt-4">
    <x-senyum-badge status="suspended">Status: Ditangguhkan</x-senyum-badge>
</div>

<ol class="mt-5 space-y-3 text-sm">
    <li class="sn-card sn-card-paper !p-3"><strong>01 — Jangan panik.</strong><br><span class="text-neutral-600">Data outlet dan riwayat Anda tetap aman.</span></li>
    <li class="sn-card sn-card-paper !p-3"><strong>02 — Hubungi developer.</strong><br><span class="text-neutral-600">Tanyakan penyebab dan syarat pengaktifan kembali.</span></li>
    <li class="sn-card sn-card-paper !p-3"><strong>03 — Tunggu pemulihan.</strong><br><span class="text-neutral-600">Setelah disetujui, Anda otomatis masuk dashboard lagi.</span></li>
</ol>

<div class="mt-5 grid gap-2">
    <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="sn-btn sn-btn-yellow sn-btn-block">Ajukan Banding via WhatsApp</a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="sn-btn sn-btn-ghost sn-btn-block">Keluar</button>
    </form>
</div>

<div class="mt-4">
    <x-senyum-alert type="action" title="Langkah berikutnya">Segera hubungi developer agar akun cepat dipulihkan.</x-senyum-alert>
</div>
@endsection
