@extends('layouts.auth')

@section('title', 'Daftar Distributor — SENYUM')
@section('meta_description', 'Daftar sebagai distributor Kretek Senyum: isi data diri, kota, kecamatan, dan kontak WhatsApp. Akun baru berstatus pending untuk direview.')

@section('content')
<h1 class="font-display uppercase text-3xl">Daftar Distributor</h1>
<p class="mt-2 text-sm text-neutral-600">Isi data dengan jujur. Akun baru berstatus <span class="font-bold">pending</span> untuk direview tim kami.</p>

<form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-5" novalidate>
    @csrf

    <div>
        <label class="sn-label" for="name">Nama Lengkap</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="cth. Budi Santoso"
            class="sn-input @error('name') sn-input--error @enderror" required autocomplete="name">
        <p class="sn-help">Nama sesuai identitas, untuk keperluan verifikasi.</p>
        @error('name')<p class="sn-error" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="sn-label" for="username">Username</label>
        <input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="cth. budi_senyum"
            class="sn-input @error('username') sn-input--error @enderror" required autocomplete="username">
        <p class="sn-help">Min. 3 karakter, huruf/angka/garis — dipakai untuk masuk.</p>
        @error('username')<p class="sn-error" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="sn-label" for="whatsapp">Nomor WhatsApp</label>
        <input id="whatsapp" name="whatsapp" type="tel" value="{{ old('whatsapp') }}" placeholder="cth. 6281234567890"
            class="sn-input @error('whatsapp') sn-input--error @enderror" required autocomplete="tel">
        <p class="sn-help">Nomor aktif untuk koordinasi wilayah dan pesanan.</p>
        @error('whatsapp')<p class="sn-error" role="alert">{{ $message }}</p>@enderror
    </div>

    <div class="grid sm:grid-cols-2 gap-5">
        <div>
            <label class="sn-label" for="city">Kota</label>
            <input id="city" name="city" type="text" value="{{ old('city') }}" placeholder="cth. Malang"
                class="sn-input @error('city') sn-input--error @enderror" required autocomplete="address-level2">
            <p class="sn-help">Kota/kabupaten wilayah distribusi.</p>
            @error('city')<p class="sn-error" role="alert">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="sn-label" for="district">Kecamatan</label>
            <input id="district" name="district" type="text" value="{{ old('district') }}" placeholder="cth. Klojen"
                class="sn-input @error('district') sn-input--error @enderror" required autocomplete="address-level3">
            <p class="sn-help">Kecamatan fokus garapan awal.</p>
            @error('district')<p class="sn-error" role="alert">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="sn-label" for="experience">Pengalaman <span class="font-normal normal-case">(opsional)</span></label>
        <select id="experience" name="experience" class="sn-input @error('experience') sn-input--error @enderror">
            <option value="">— Pilih yang paling sesuai —</option>
            <option value="Baru mulai" {{ old('experience') === 'Baru mulai' ? 'selected' : '' }}>Baru mulai</option>
            <option value="Pernah jualan" {{ old('experience') === 'Pernah jualan' ? 'selected' : '' }}>Pernah jualan</option>
            <option value="Pengalaman distribusi" {{ old('experience') === 'Pengalaman distribusi' ? 'selected' : '' }}>Pengalaman distribusi</option>
        </select>
        <p class="sn-help">Boleh dikosongkan. Membantu tim memahami latar belakangmu.</p>
        @error('experience')<p class="sn-error" role="alert">{{ $message }}</p>@enderror
    </div>

    <div class="grid sm:grid-cols-2 gap-5">
        <div>
            <label class="sn-label" for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Min. 8 karakter"
                class="sn-input @error('password') sn-input--error @enderror" required autocomplete="new-password">
            @error('password')<p class="sn-error" role="alert">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="sn-label" for="password_confirmation">Konfirmasi Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi password"
                class="sn-input" required autocomplete="new-password">
        </div>
    </div>

    <button type="submit" class="sn-btn sn-btn-primary w-full">Daftar</button>
</form>

<p class="mt-4 text-sm text-neutral-600 text-center">Setelah daftar, status akunmu <span class="font-bold">pending</span> — tim kami akan meninjau sebelum workspace dibuka.</p>
<p class="mt-2 text-sm text-center">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold underline underline-offset-4">Masuk</a></p>
@endsection
