@extends('layouts.distributor')

@section('title', 'Outlet Saya — Field Workspace')

@section('content')
<p class="sn-kicker">Network &middot; Outlet</p>
<div class="flex flex-wrap items-end gap-3 mt-1">
    <h1 class="font-display font-black uppercase text-2xl sm:text-3xl">Outlet Saya</h1>
    <a href="{{ route('distributor.outlet.create') }}" class="sn-btn sn-btn-yellow sn-btn-sm ml-auto">+ Tambah Outlet</a>
</div>
<p class="mt-1 text-sm text-neutral-600">Cari outlet, lihat status, dan nonaktifkan yang sudah tutup. Cari berdasarkan nama, kota, atau kecamatan.</p>

@if (session('success'))
    <div class="mt-4"><x-senyum-alert type="success" :message="session('success')" /></div>
@endif

<div class="mt-5">
    @livewire('distributor.outlet-form')
</div>

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">Outlet baru? <a class="underline font-bold" href="{{ route('distributor.outlet.create') }}">Tambahkan sekarang</a>, lalu <a class="underline font-bold" href="{{ route('distributor.visit') }}">jadwalkan kunjungan</a> pertama.</x-senyum-alert>
</div>
@endsection
