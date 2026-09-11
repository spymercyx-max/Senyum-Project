@extends('layouts.distributor')

@section('title', $outlet->name . ' — Outlet')

@section('content')
<p class="sn-kicker">Network &middot; Detail Outlet</p>
<div class="flex flex-wrap items-center gap-2 mt-1">
    <h1 class="font-display font-black uppercase text-2xl sm:text-3xl">{{ $outlet->name }}</h1>
    <x-senyum-badge :status="$outlet->status">{{ $outlet->status === 'active' ? 'Aktif' : 'Nonaktif' }}</x-senyum-badge>
</div>

@if (session('success'))
    <div class="mt-4"><x-senyum-alert type="success" :message="session('success')" /></div>
@endif
@if ($errors->any())
    <div class="mt-4"><x-senyum-alert type="danger" title="Belum bisa disimpan"><ul class="list-disc ml-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></x-senyum-alert></div>
@endif

<div class="flex flex-wrap gap-2 mt-4">
    <a href="{{ route('distributor.outlet.edit', $outlet) }}" class="sn-btn sn-btn-ghost sn-btn-sm">Edit</a>
    <a href="{{ route('distributor.outlet') }}" class="sn-btn sn-btn-ghost sn-btn-sm">← Semua Outlet</a>
</div>

<div class="grid gap-3 mt-5 lg:grid-cols-2">
    <div class="sn-card">
        <p class="sn-kicker mb-2">Identitas</p>
        <dl class="text-sm space-y-1.5">
            <div><dt class="sn-label !mb-0">Alamat</dt><dd>{{ $outlet->address }}, {{ $outlet->district }}, {{ $outlet->city }}</dd></div>
            <div><dt class="sn-label !mb-0">Telepon</dt><dd>{{ $outlet->phone ?? '—' }}</dd></div>
            <div><dt class="sn-label !mb-0">Wilayah</dt><dd>{{ $outlet->territory?->displayName() ?? '—' }}</dd></div>
            @if ($outlet->notes)<div><dt class="sn-label !mb-0">Catatan</dt><dd>{{ $outlet->notes }}</dd></div>@endif
        </dl>
    </div>
    <div class="sn-card sn-card-cyan">
        <p class="sn-kicker mb-2">Lokasi</p>
        @if ($outlet->google_maps_url)
            <p class="text-sm break-all"><span class="sn-label !mb-0 block">Link asli</span><a href="{{ $outlet->google_maps_url }}" target="_blank" rel="noopener" class="underline font-bold">{{ \Str::limit($outlet->google_maps_url, 80) }}</a></p>
        @endif
        @if ($outlet->hasCoordinates())
            <p class="sn-label !mb-0 mt-3">Koordinat</p>
            <p class="font-mono text-sm">{{ $outlet->latitude }}, {{ $outlet->longitude }}</p>
            <div class="mt-3 border-2 border-black rounded-sm overflow-hidden bg-white">
                <iframe title="Peta lokasi {{ $outlet->name }}" src="https://www.google.com/maps?q={{ $outlet->latitude }},{{ $outlet->longitude }}&output=embed" class="w-full" height="240" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <div class="grid gap-2 sm:flex mt-3">
                <a class="sn-btn sn-btn-primary sn-btn-sm w-full sm:w-auto" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query={{ $outlet->latitude }},{{ $outlet->longitude }}">Buka Rute</a>
                <a class="sn-btn sn-btn-ghost sn-btn-sm w-full sm:w-auto" href="{{ route('distributor.peta') }}">Lihat di Peta</a>
            </div>
        @else
            <p class="sn-help mt-2">Belum ada titik lokasi.</p>
        @endif
    </div>
</div>

<h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Galeri ({{ $images->count() }}/5)</h2>
@if ($images->isEmpty())
    <p class="text-sm text-neutral-500">Belum ada foto outlet.</p>
@else
    <div class="border-2 border-black rounded-sm bg-white">
        <img id="outlet-main-photo" src="{{ $images->first()->url() }}" alt="Foto utama {{ $outlet->name }}" class="w-full max-h-[420px] object-contain bg-white" onerror="this.style.display='none';document.getElementById('outlet-main-fallback').style.display='flex';">
        <div id="outlet-main-fallback" style="display:none;" class="w-full max-h-[420px] min-h-[160px] items-center justify-center bg-white text-sm text-neutral-500 p-6">Foto tidak dapat dimuat</div>
    </div>
    @if ($images->count() > 1)
        <div class="flex gap-2 overflow-x-auto mt-2 pb-1 sm:grid sm:grid-cols-5 sm:overflow-visible">
            @foreach ($images as $idx => $img)
                <button type="button" data-outlet-thumb data-src="{{ $img->url() }}" class="border-2 border-black rounded-sm bg-white shrink-0 w-24 sm:w-auto p-1" aria-label="Lihat foto {{ $idx + 1 }}">
                    <img src="{{ $img->url() }}" alt="Foto {{ $idx + 1 }} {{ $outlet->name }}" class="w-full h-16 object-contain bg-white" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <span style="display:none;" class="w-full h-16 items-center justify-center text-xs text-neutral-500 text-center">Foto tidak dapat dimuat</span>
                </button>
            @endforeach
        </div>
        <script>
        (function () {
            var main = document.getElementById('outlet-main-photo');
            var fallback = document.getElementById('outlet-main-fallback');
            document.querySelectorAll('[data-outlet-thumb]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!main) return;
                    main.style.display = '';
                    if (fallback) fallback.style.display = 'none';
                    main.src = btn.getAttribute('data-src');
                });
            });
        })();
        </script>
    @endif
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-3">
        @foreach ($images as $img)
            <div class="border-2 border-black rounded-sm overflow-hidden bg-white">
                <p class="text-xs font-bold px-2 pt-2 truncate">{{ basename($img->url()) }}</p>
                <form method="POST" action="{{ route('distributor.outlet.images.destroy', [$outlet, $img]) }}" class="p-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="sn-btn sn-btn-ghost sn-btn-sm w-full">Hapus</button>
                </form>
            </div>
        @endforeach
    </div>
@endif
@if ($images->count() < 5)
    <form method="POST" action="{{ route('distributor.outlet.images.store', $outlet) }}" enctype="multipart/form-data" class="sn-card mt-3">
        @csrf
        <label class="sn-label" for="d-images">Tambah foto (sisa {{ 5 - $images->count() }})</label>
        <div class="flex flex-col sm:flex-row gap-2">
            <input id="d-images" type="file" name="images[]" class="sn-input" accept="image/jpeg,image/png,image/webp" multiple required>
            <button type="submit" class="sn-btn sn-btn-yellow sn-btn-sm w-full sm:w-auto whitespace-nowrap">Upload</button>
        </div>
        @error('images')<p class="sn-error mt-1">{{ $message }}</p>@enderror
    </form>
@endif

<h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Penjualan Terakhir</h2>
@if ($transactions->isEmpty())
    <x-senyum-empty-state title="Belum ada penjualan" message="Catat penjualan pertama ke outlet ini." action="Buat Pesanan" :href="route('distributor.pemesanan')" />
@else
    <div class="sn-table-wrap hidden md:block">
        <table class="sn-table">
            <thead><tr><th>Kode</th><th class="sn-num">Total</th><th>Tanggal</th></tr></thead>
            <tbody>
                @foreach ($transactions as $t)
                    <tr><td class="font-mono font-bold">{{ $t->code }}</td><td class="sn-num">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td><td>{{ $t->sold_at?->format('d M Y') ?? $t->created_at->format('d M Y') }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="grid gap-2 md:hidden">
        @foreach ($transactions as $t)
            <div class="sn-card !p-3 text-sm"><strong class="font-mono">{{ $t->code }}</strong><span class="block text-neutral-600">{{ $t->sold_at?->format('d M Y') ?? $t->created_at->format('d M Y') }}</span><strong>Rp {{ number_format($t->total_amount, 0, ',', '.') }}</strong></div>
        @endforeach
    </div>
@endif

<h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Kunjungan Terakhir</h2>
@if ($visits->isEmpty())
    <x-senyum-empty-state title="Belum ada kunjungan" message="Datangi outlet ini dan catat kunjunganmu." action="Catat Visit" :href="route('distributor.visit')" />
@else
    <div class="grid gap-2">
        @foreach ($visits as $v)
            <div class="sn-card !p-3 text-sm">
                <strong>{{ $v->visited_at?->format('d M Y') }}</strong>
                <x-senyum-badge :status="$v->status">{{ $v->status }}</x-senyum-badge>
                @if ($v->notes)<span class="block text-neutral-600 mt-1">{{ $v->notes }}</span>@endif
                @if ($v->follow_up_at)<span class="block text-xs mt-1">Tindak lanjut: {{ $v->follow_up_at->format('d M Y') }}</span>@endif
            </div>
        @endforeach
    </div>
@endif

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">Jual ke outlet ini lewat <a class="underline font-bold" href="{{ route('distributor.pemesanan') }}">pemesanan</a> atau catat <a class="underline font-bold" href="{{ route('distributor.visit') }}">kunjungan</a> berikutnya.</x-senyum-alert>
</div>
@endsection
