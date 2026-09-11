@extends('layouts.auth')

@section('title', 'Pengajuan Ditolak — SENYUM')

@section('content')
<p class="sn-kicker">Senyum Field Workspace</p>
<h1 class="sn-card-title mt-2">Pengajuan Belum Lolos</h1>
<p class="mt-1 text-sm">Halo, <strong>{{ auth()->user()->name }}</strong>. Mohon maaf, pengajuan distributor Anda belum bisa kami setujui saat ini.</p>

<div class="mt-4">
    <x-senyum-badge status="rejected">Status: Ditolak</x-senyum-badge>
</div>

<ol class="mt-5 space-y-3 text-sm">
    <li class="sn-card sn-card-paper !p-3"><strong>01 — Cari tahu alasannya.</strong><br><span class="text-neutral-600">Hubungi developer lewat WhatsApp untuk penjelasan.</span></li>
    <li class="sn-card sn-card-paper !p-3"><strong>02 — Perbaiki data Anda.</strong><br><span class="text-neutral-600">Lengkapi profil, wilayah, dan pengalaman usaha.</span></li>
    <li class="sn-card sn-card-paper !p-3"><strong>03 — Ajukan ulang.</strong><br><span class="text-neutral-600">Setelah diperbaiki, Anda bisa mendaftar kembali.</span></li>
</ol>

<div class="mt-5 grid gap-2">
    <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="sn-btn sn-btn-yellow sn-btn-block">Tanya via WhatsApp</a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="sn-btn sn-btn-ghost sn-btn-block">Keluar</button>
    </form>
</div>

<div class="mt-4">
    <x-senyum-alert type="action" title="Langkah berikutnya">Chat developer sekarang untuk tahu apa yang perlu diperbaiki.</x-senyum-alert>
</div>
@endsection
