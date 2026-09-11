@extends('layouts.distributor')

@section('title', 'Profil Saya — Field Workspace')

@section('content')
<p class="sn-kicker">System &middot; Profil</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Profil Saya</h1>
<p class="mt-1 text-sm text-neutral-600">Data dirimu dan wilayah kerjamu. Wilayah tidak bisa diubah sendiri — hubungi developer bila pindah area.</p>

<div class="grid gap-3 mt-5 lg:grid-cols-2">
    <div class="sn-card">
        <p class="sn-kicker mb-2">Akun</p>
        <dl class="text-sm space-y-1.5">
            <div><dt class="sn-label !mb-0">Nama</dt><dd>{{ $user->name }}</dd></div>
            <div><dt class="sn-label !mb-0">Email / Username</dt><dd>{{ $user->email ?? $user->username ?? '—' }}</dd></div>
            <div><dt class="sn-label !mb-0">Telepon</dt><dd>{{ $user->phone ?? '—' }}</dd></div>
            <div><dt class="sn-label !mb-0">Status</dt><dd><x-senyum-badge :status="$user->distributorStatus() ?? 'pending'">{{ $user->distributorStatus() ?? 'pending' }}</x-senyum-badge></dd></div>
        </dl>
    </div>
    <div class="sn-card sn-card-cyan">
        <p class="sn-kicker mb-2">Wilayah Kerja (terkunci)</p>
        <p class="font-display font-black uppercase text-xl">{{ $territory ?? 'Belum dipasang' }}</p>
        @if ($territoryModel)
            <p class="text-sm mt-1">Kota: {{ $territoryModel->city }} &middot; Kecamatan: {{ $territoryModel->district }}</p>
        @endif
        <p class="sn-help !text-[#062A30] mt-2">Mau pindah wilayah? Chat developer lewat halaman bantuan.</p>
    </div>
</div>

<div class="mt-5">
    @livewire('distributor.profile-form')
</div>

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">Profil lengkap bikin developer cepat percaya. Pastikan nomor WhatsApp aktif.</x-senyum-alert>
</div>
@endsection
