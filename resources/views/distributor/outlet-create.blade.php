@extends('layouts.distributor')

@section('title', 'Tambah Outlet — Field Workspace')

@section('content')
<p class="sn-kicker">Network &middot; Outlet Baru</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Tambah Outlet</h1>
<p class="mt-1 text-sm text-neutral-600">Isi data warung/toko yang jadi mitra jualanmu. Tempel link Google Maps outlet — koordinat dibaca otomatis, tanpa isi manual.</p>

@if ($errors->any())
    <div class="mt-4"><x-senyum-alert type="danger" title="Belum bisa disimpan"><ul class="list-disc ml-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-senyum-alert></div>
@endif

<div class="sn-card sn-card-cyan mt-5 !p-4 text-sm">
    <strong>Wilayah terkunci:</strong> {{ $territory ?? '—' }}. Outlet barumu otomatis masuk wilayah kerjamu.
</div>

<form method="POST" action="{{ route('distributor.outlet.store') }}" enctype="multipart/form-data" class="sn-card mt-4">
    @csrf
    <div class="grid gap-4">
        <div>
            <label class="sn-label" for="o-name">Nama outlet</label>
            <input id="o-name" type="text" name="name" class="sn-input @error('name') sn-input--error @enderror" value="{{ old('name') }}" placeholder="Contoh: Warung Berkah Jaya" required>
            <p class="sn-help">Nama warung/toko seperti di papan nama.</p>
            @error('name')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="sn-label" for="o-address">Alamat lengkap</label>
            <textarea id="o-address" name="address" class="sn-textarea @error('address') sn-textarea--error @enderror" rows="2" placeholder="Jalan, nomor, patokan..." required>{{ old('address') }}</textarea>
            @error('address')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="sn-label" for="o-city">Kota</label>
                <input id="o-city" type="text" name="city" class="sn-input @error('city') sn-input--error @enderror" value="{{ old('city', $territoryModel?->city) }}" required>
                @error('city')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="sn-label" for="o-district">Kecamatan</label>
                <input id="o-district" type="text" name="district" class="sn-input @error('district') sn-input--error @enderror" value="{{ old('district', $territoryModel?->district) }}" required>
                @error('district')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="sn-label" for="o-phone">No. HP outlet (opsional)</label>
            <input id="o-phone" type="text" name="phone" class="sn-input @error('phone') sn-input--error @enderror" value="{{ old('phone') }}" placeholder="08...">
            @error('phone')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <livewire:distributor.outlet-location-picker :value="old('google_maps_url', '')" />
            @error('google_maps_url')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="sn-label" for="o-images">Foto outlet (opsional, maks 5)</label>
            <input id="o-images" type="file" name="images[]" class="sn-input @error('images') sn-input--error @enderror" accept="image/jpeg,image/png,image/webp" multiple>
            <p class="sn-help">Format JPG/PNG/WebP, maks 5 MB per foto.</p>
            @error('images')<p class="sn-error">{{ $message }}</p>@enderror
            @error('images.*')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="sn-label" for="o-notes">Catatan (opsional)</label>
            <textarea id="o-notes" name="notes" class="sn-textarea" rows="2" placeholder="Contoh: buka jam 7 pagi–9 malam">{{ old('notes') }}</textarea>
            @error('notes')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-2 sm:flex">
            <button type="submit" class="sn-btn sn-btn-yellow w-full sm:w-auto">Simpan Outlet</button>
            <a href="{{ route('distributor.outlet') }}" class="sn-btn sn-btn-ghost w-full sm:w-auto">Batal</a>
        </div>
    </div>
</form>

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">Setelah tersimpan, catat <a class="underline font-bold" href="{{ route('distributor.visit') }}">kunjungan pertama</a> ke outlet ini.</x-senyum-alert>
</div>
@endsection
