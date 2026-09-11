@extends('layouts.distributor')

@section('title', 'Edit Outlet — Field Workspace')

@section('content')
<p class="sn-kicker">Network &middot; Edit Outlet</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Edit {{ $outlet->name }}</h1>
<p class="mt-1 text-sm text-neutral-600">Ubah data outlet. Jika link Google Maps diganti, koordinat dibaca ulang otomatis.</p>

@if ($errors->any())
    <div class="mt-4"><x-senyum-alert type="danger" title="Belum bisa disimpan"><ul class="list-disc ml-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-senyum-alert></div>
@endif

<div class="sn-card sn-card-cyan mt-5 !p-4 text-sm">
    <strong>Wilayah terkunci:</strong> {{ $territory ?? '—' }}. Outlet tetap di wilayah kerjamu.
</div>

<form method="POST" action="{{ route('distributor.outlet.update', $outlet) }}" enctype="multipart/form-data" class="sn-card mt-4">
    @csrf
    @method('PUT')
    <div class="grid gap-4">
        <div>
            <label class="sn-label" for="e-name">Nama outlet</label>
            <input id="e-name" type="text" name="name" class="sn-input @error('name') sn-input--error @enderror" value="{{ old('name', $outlet->name) }}" required>
            @error('name')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="sn-label" for="e-address">Alamat lengkap</label>
            <textarea id="e-address" name="address" class="sn-textarea @error('address') sn-textarea--error @enderror" rows="2" required>{{ old('address', $outlet->address) }}</textarea>
            @error('address')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="sn-label" for="e-city">Kota</label>
                <input id="e-city" type="text" name="city" class="sn-input @error('city') sn-input--error @enderror" value="{{ old('city', $outlet->city) }}" required>
                @error('city')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="sn-label" for="e-district">Kecamatan</label>
                <input id="e-district" type="text" name="district" class="sn-input @error('district') sn-input--error @enderror" value="{{ old('district', $outlet->district) }}" required>
                @error('district')<p class="sn-error">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="sn-label" for="e-phone">No. HP outlet (opsional)</label>
            <input id="e-phone" type="text" name="phone" class="sn-input @error('phone') sn-input--error @enderror" value="{{ old('phone', $outlet->phone) }}" placeholder="08...">
            @error('phone')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <livewire:distributor.outlet-location-picker :value="old('google_maps_url', $outlet->google_maps_url ?? '')" />
            <p class="sn-help">Ganti link bila titik pindah. Koordinat lama: <span class="font-mono">{{ $outlet->latitude }}, {{ $outlet->longitude }}</span></p>
            @error('google_maps_url')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="sn-label" for="e-images">Tambah foto (opsional, total maks 5 — tersimpan {{ $images->count() }})</label>
            <input id="e-images" type="file" name="images[]" class="sn-input @error('images') sn-input--error @enderror" accept="image/jpeg,image/png,image/webp" multiple>
            @error('images')<p class="sn-error">{{ $message }}</p>@enderror
            @error('images.*')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="sn-label" for="e-notes">Catatan (opsional)</label>
            <textarea id="e-notes" name="notes" class="sn-textarea" rows="2">{{ old('notes', $outlet->notes) }}</textarea>
            @error('notes')<p class="sn-error">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-2 sm:flex">
            <button type="submit" class="sn-btn sn-btn-yellow w-full sm:w-auto">Simpan Perubahan</button>
            <a href="{{ route('distributor.outlet.show', $outlet) }}" class="sn-btn sn-btn-ghost w-full sm:w-auto">Batal</a>
        </div>
    </div>
</form>

@if ($images->isNotEmpty())
    <h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Foto Tersimpan ({{ $images->count() }}/5)</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
        @foreach ($images as $img)
            <div class="border-2 border-black rounded-sm overflow-hidden bg-white">
                <img src="{{ $img->url() }}" alt="Foto {{ $outlet->name }}" class="w-full h-28 object-cover" loading="lazy">
                <form method="POST" action="{{ route('distributor.outlet.images.destroy', [$outlet, $img]) }}" class="p-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="sn-btn sn-btn-ghost sn-btn-sm w-full">Hapus</button>
                </form>
            </div>
        @endforeach
    </div>
@endif
@endsection
