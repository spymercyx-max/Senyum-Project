@extends('layouts.auth')

@section('title', 'Masuk — SENYUM')
@section('meta_description', 'Masuk ke akun SENYUM untuk mengelola pesanan, outlet, dan kemitraan distributor.')

@section('content')
<h1 class="font-display uppercase text-3xl">Masuk</h1>
<p class="mt-2 text-sm text-neutral-600">Satu akun untuk distributor dan developer. Khusus 18+.</p>

@if ($errors->any())
    <div class="mt-4">
        <x-senyum-alert type="danger" title="Tidak bisa masuk">
            Periksa kembali username/email dan password kamu.
        </x-senyum-alert>
    </div>
@endif

<form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5" novalidate>
    @csrf

    <div>
        <label class="sn-label" for="login">Username atau Email</label>
        <input
            id="login"
            name="login"
            type="text"
            class="sn-input @error('login') sn-input--error @enderror"
            value="{{ old('login') }}"
            placeholder="cth. budi_senyum"
            required
            autofocus
            autocomplete="username"
        >
        @error('login')
            <p class="sn-error" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="sn-label" for="password">Password</label>
        <input
            id="password"
            name="password"
            type="password"
            class="sn-input @error('password') sn-input--error @enderror"
            placeholder="••••••••"
            required
            autocomplete="current-password"
        >
        @error('password')
            <p class="sn-error" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-2">
        <input id="remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }} class="h-4 w-4 accent-black">
        <label for="remember" class="text-sm">Ingat saya di perangkat ini</label>
    </div>

    <button type="submit" class="sn-btn sn-btn-primary w-full">Masuk</button>
</form>

<p class="mt-6 text-sm text-center">
    Belum punya akun?
    <a href="{{ route('register') }}" class="font-bold underline underline-offset-4">Daftar sebagai Distributor</a>
</p>
@endsection
