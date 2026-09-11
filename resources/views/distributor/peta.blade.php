@extends('layouts.distributor')

@section('title', 'Peta Outlet — Field Workspace')

@section('content')
<p class="sn-kicker">Network &middot; Peta</p>
<h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Peta Outlet</h1>
<p class="mt-1 text-sm text-neutral-600">{{ collect($outletsJson)->whereNotNull('lat')->count() }} dari {{ count($outletsJson) }} outlet punya titik lokasi. Ketuk marker untuk detail, atau tombol untuk buka rute.</p>

@if (empty($outletsJson))
    <div class="mt-5"><x-senyum-empty-state title="Belum ada outlet" message="Tambahkan outlet dulu agar peta terisi." action="Tambah Outlet" :href="route('distributor.outlet.create')" /></div>
@else
    @if (empty($mapsKey))
        <div class="sn-card sn-card-yellow mt-5" role="alert">
            <p class="font-display font-black uppercase">Map sedang tidak tersedia.</p>
            <p class="text-sm mt-1">Kunci peta belum dipasang. Daftar outlet di bawah tetap bisa dipakai — tombol Rute membuka Google Maps langsung.</p>
        </div>
    @endif

    <div id="dist-map" class="mt-5 border-[3px] border-black rounded-sm bg-white overflow-hidden {{ empty($mapsKey) ? 'hidden' : '' }}" style="min-height: 380px;" role="application" aria-label="Peta interaktif outlet"></div>

    <h2 class="font-display font-black uppercase text-lg mt-7 mb-3">Daftar Outlet</h2>
    <div class="grid gap-2">
        @foreach ($outletsJson as $o)
            <div class="sn-card !p-4" data-outlet-row data-lat="{{ $o['lat'] }}" data-lng="{{ $o['lng'] }}">
                <div class="flex flex-wrap items-center gap-2">
                    <strong>{{ $o['nama'] }}</strong>
                    <x-senyum-badge :status="$o['status']">{{ $o['status'] === 'active' ? 'Aktif' : 'Nonaktif' }}</x-senyum-badge>
                </div>
                <p class="text-sm text-neutral-600 mt-1">{{ $o['alamat'] }}, {{ $o['kecamatan'] }}, {{ $o['kota'] }}</p>
                @if ($o['lat'] && $o['lng'])
                    <p class="font-mono text-xs mt-1">{{ $o['lat'] }}, {{ $o['lng'] }}</p>
                @endif
                @if ($o['last_visit'] || $o['last_transaksi'])
                    <p class="text-xs text-neutral-500 mt-1">Visit terakhir: {{ $o['last_visit'] ?? '—' }} &middot; Transaksi terakhir: {{ $o['last_transaksi'] ?? '—' }}</p>
                @endif
                <div class="flex flex-wrap gap-2 mt-2">
                    <a href="{{ $o['url']['detail'] }}" class="sn-btn sn-btn-ghost sn-btn-sm">Detail</a>
                    @if (! empty($o['url']['route']))
                        <a href="{{ $o['url']['route'] }}" target="_blank" rel="noopener" class="sn-btn sn-btn-cyan sn-btn-sm">Rute</a>
                    @endif
                    <a href="{{ $o['url']['edit'] }}" class="sn-btn sn-btn-ghost sn-btn-sm">Edit</a>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="mt-5">
    <x-senyum-alert type="action" title="Langkah berikutnya">Rencanakan rute kunjungan hari ini, lalu <a class="underline font-bold" href="{{ route('distributor.visit') }}">catat setiap kunjungan</a>.</x-senyum-alert>
</div>

@push('scripts')
<script>
(function () {
    var outlets = @json($outletsJson);
    var key = @json($mapsKey);
    var center = @json($center);
    var el = document.getElementById('dist-map');
    if (!el || !outlets.length) return;
    if (!key) return;

    window.__distMapInit = function () {
        var map = new google.maps.Map(el, {
            center: { lat: Number(center.lat), lng: Number(center.lng) },
            zoom: Number(center.zoom) || 6,
        });
        var bounds = new google.maps.LatLngBounds();
        var hasPoint = false;

        outlets.forEach(function (o) {
            if (o.lat === null || o.lng === null) return;
            var pos = { lat: Number(o.lat), lng: Number(o.lng) };
            hasPoint = true;
            bounds.extend(pos);
            var marker = new google.maps.Marker({ position: pos, map: map, title: o.nama });
            var html = '<div style="max-width:240px;font-size:13px">'
                + '<strong>' + o.nama + '</strong><br>'
                + o.alamat + ', ' + o.kecamatan + ', ' + o.kota + '<br>'
                + 'Status: ' + o.status + '<br>'
                + 'Visit terakhir: ' + (o.last_visit || '—') + '<br>'
                + 'Transaksi terakhir: ' + (o.last_transaksi || '—') + '<br>'
                + '<div style="margin-top:6px;display:flex;gap:6px">'
                + '<a href="' + o.url.detail + '">Detail</a>'
                + (o.url.route ? ' · <a href="' + o.url.route + '" target="_blank" rel="noopener">Rute</a>' : '')
                + ' · <a href="' + o.url.edit + '">Edit</a>'
                + '</div></div>';
            var win = new google.maps.InfoWindow({ content: html });
            marker.addListener('click', function () { win.open(map, marker); });
        });

        if (hasPoint) map.fitBounds(bounds);
    };

    var s = document.createElement('script');
    s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(key) + '&callback=__distMapInit';
    s.async = true;
    s.defer = true;
    document.head.appendChild(s);
})();
</script>
@endpush
@endsection
