@extends('layouts.developer')

@section('title', 'Peta Strategis — SENYUM Command Center')

@section('content')
<div class="space-y-6">
    <div>
        <p class="sn-kicker">Network · Peta Strategis</p>
        <h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">Peta Strategis</h1>
        <p class="text-sm text-neutral-600 mt-1">Sebaran territory, distributor, outlet, dan aktivitas lapangan dari data nyata.</p>
    </div>

    {{-- Statistik dari /statistics --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3" id="sn-map-stats" aria-live="polite">
        <x-senyum-stat label="Total Territories" value="—" />
        <x-senyum-stat label="Total Distributors" value="—" />
        <x-senyum-stat label="Total Outlets" value="—" />
        <x-senyum-stat label="Active Outlets" value="—" />
        <x-senyum-stat label="Coverage Rate" value="—" />
        <x-senyum-stat label="Active Areas" value="—" />
        <x-senyum-stat label="Outlet lokasi valid" value="—" />
        <x-senyum-stat label="Belum valid" value="—" />
    </div>
    <p class="text-sm">Outlet lokasi valid: <strong id="sn-valid-count">—</strong> + Belum valid: <strong id="sn-invalid-count">—</strong> &middot; <a class="underline font-bold" href="{{ route('developer.outlet') }}">Kelola outlet</a></p>

    <div class="flex flex-col gap-2 lg:flex-row lg:items-start">
        <div class="flex-1 border-2 border-black rounded-md bg-white p-2">
            <label class="sn-label" for="sn-search">Cari</label>
            <div class="flex gap-1">
                <input id="sn-search" type="search" class="sn-input" placeholder="Outlet / distributor / kota…" autocomplete="off" minlength="2">
                <button type="button" id="sn-search-btn" class="sn-btn sn-btn-tosca sn-btn-sm shrink-0">Cari</button>
            </div>
            <div id="sn-search-results" class="mt-2 space-y-1"></div>
        </div>

        <details class="flex-1 border-2 border-black rounded-md bg-white p-2">
            <summary class="sn-label" style="cursor:pointer;">Layer</summary>
            <div class="flex flex-wrap gap-2 mt-2" role="group" aria-label="Layer peta">
                <label class="text-xs font-bold"><input type="checkbox" data-layer="territory" checked> Territory</label>
                <label class="text-xs font-bold"><input type="checkbox" data-layer="coverage" checked> Coverage</label>
                <label class="text-xs font-bold"><input type="checkbox" data-layer="distributor" checked> Distributor</label>
                <label class="text-xs font-bold"><input type="checkbox" data-layer="outlet" checked> Outlet</label>
                <label class="text-xs font-bold"><input type="checkbox" data-layer="activity"> Activity</label>
            </div>
        </details>

        <details class="flex-1 border-2 border-black rounded-md bg-white p-2">
            <summary class="sn-label" style="cursor:pointer;">Filter</summary>
            <div class="grid grid-cols-2 gap-2 mt-2">
                <div><label class="sn-label" for="f-city">Kota</label><input id="f-city" type="text" class="sn-input" placeholder="Semua"></div>
                <div><label class="sn-label" for="f-district">Kecamatan</label><input id="f-district" type="text" class="sn-input" placeholder="Semua"></div>
                <div><label class="sn-label" for="f-outlet-status">Status outlet</label>
                    <select id="f-outlet-status" class="sn-select"><option value="">Semua</option><option value="active">Aktif</option><option value="inactive">Nonaktif</option><option value="pending">Menunggu</option></select>
                </div>
                <div><label class="sn-label" for="f-dist-status">Status distributor</label>
                    <select id="f-dist-status" class="sn-select"><option value="">Semua</option><option value="pending">Pending</option><option value="approved">Disetujui</option><option value="rejected">Ditolak</option><option value="suspended">Ditangguhkan</option></select>
                </div>
                <div><label class="sn-label" for="f-date-from">Dari tanggal</label><input id="f-date-from" type="date" class="sn-input"></div>
                <div><label class="sn-label" for="f-date-to">Sampai tanggal</label><input id="f-date-to" type="date" class="sn-input"></div>
                <div class="col-span-2"><label class="sn-label" for="f-volume">Min. volume (Rp)</label><input id="f-volume" type="number" min="0" class="sn-input" placeholder="0"></div>
            </div>
            <button type="button" id="sn-filter-btn" class="sn-btn sn-btn-primary sn-btn-sm mt-2">Terapkan filter</button>
        </details>
    </div>

    <div class="sn-map-shell">
        <div id="sn-map" class="sn-map-canvas min-h-[70vh]" style="min-height:70vh;" role="application" aria-label="Peta strategis distribusi SENYUM"></div>

        <div id="sn-map-drawer" class="sn-map-drawer hidden" role="dialog" aria-label="Detail titik peta">
            <div class="flex items-start gap-2">
                <h2 id="sn-drawer-title" class="font-display font-bold uppercase text-sm flex-1">Detail</h2>
                <button type="button" id="sn-drawer-close" class="sn-btn sn-btn-ghost sn-btn-sm" aria-label="Tutup detail">Tutup</button>
            </div>
            <div id="sn-drawer-body" class="text-sm mt-2 space-y-2"></div>
        </div>

        <div id="sn-map-fallback" class="sn-map-fallback hidden" role="alert">
            <p class="font-display font-bold uppercase text-sm">Map sedang tidak tersedia.</p>
            <p class="text-xs mt-1">Statistik dan daftar di halaman ini tetap tampil dari data terbaru.</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <x-senyum-card title="Daftar wilayah" kicker="Territories">
            <div id="sn-list-territories" class="space-y-2 text-sm">
                <p class="sn-help">Memuat…</p>
            </div>
        </x-senyum-card>
        <x-senyum-card title="Daftar titik" kicker="Outlets">
            <div id="sn-list-outlets" class="space-y-2 text-sm">
                <p class="sn-help">Memuat…</p>
            </div>
        </x-senyum-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var API = @json($apiBase);
    var CENTER = @json($center);
    var MAPS_KEY = @json($mapsKey);

    var state = {
        map: null,
        mapReady: false,
        layers: { territory: true, coverage: true, distributor: true, outlet: true, activity: false },
        filters: {},
        territoryShapes: [],
        distributorMarkers: [],
        outletMarkers: [],
        activityMarkers: [],
        territories: [],
        bboxTimer: null,
        infoWin: null,
        outletById: {},
        fitNext: true,
    };

    function qs(id) { return document.getElementById(id); }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function gmapsDir(lat, lng) {
        return 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(lat + ',' + lng);
    }
    function fetchJSON(url) {
        return fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); });
    }
    function withParams(path, extra) {
        var p = new URLSearchParams();
        Object.keys(state.filters).forEach(function (k) {
            if (state.filters[k] !== '' && state.filters[k] != null) p.append(k, state.filters[k]);
        });
        Object.keys(extra || {}).forEach(function (k) { p.append(k, extra[k]); });
        var q = p.toString();
        return API + path + (q ? '?' + q : '');
    }

    /* ---------- statistik ---------- */
    function loadStats() {
        fetchJSON(withParams('/statistics'))
            .then(function (json) {
                var d = json.data || {};
                var vals = [
                    d.total_territories != null ? d.total_territories : '—',
                    d.total_distributors != null ? d.total_distributors : '—',
                    d.total_outlets != null ? d.total_outlets : '—',
                    d.active_outlets != null ? d.active_outlets : '—',
                    (d.coverage_rate != null ? d.coverage_rate + '%' : '—'),
                    d.active_areas != null ? d.active_areas : '—',
                    d.outlets_with_valid_location != null ? d.outlets_with_valid_location : '—',
                    d.outlets_without_valid_location != null ? d.outlets_without_valid_location : '—',
                ];
                var cards = document.querySelectorAll('#sn-map-stats .sn-stat-value');
                cards.forEach(function (el, i) { if (vals[i] !== undefined) el.textContent = vals[i]; });
                if (qs('sn-valid-count')) qs('sn-valid-count').textContent = vals[6];
                if (qs('sn-invalid-count')) qs('sn-invalid-count').textContent = vals[7];
            })
            .catch(function () {
                var cards = document.querySelectorAll('#sn-map-stats .sn-stat-value');
                cards.forEach(function (el) { el.textContent = '—'; });
            });
    }

    /* ---------- daftar (tetap tampil tanpa/tanpa-gagal peta) ---------- */
    function renderLists() {
        var tBox = qs('sn-list-territories');
        var oBox = qs('sn-list-outlets');
        fetchJSON(withParams('/territories'))
            .then(function (json) {
                var rows = (json.data || []).slice(0, 10);
                state.territories = json.data || [];
                if (!rows.length) { tBox.innerHTML = '<p class="sn-help">Belum ada wilayah.</p>'; return; }
                tBox.innerHTML = rows.map(function (t) {
                    return '<div class="border-2 border-black rounded-md px-3 py-2 bg-white">' +
                        '<p class="font-bold">' + esc(t.city) + ' – ' + esc(t.district) + '</p>' +
                        '<p class="text-xs text-neutral-500">' + (t.outlets_count || 0) + ' outlet · ' + (t.active_outlets_count || 0) + ' aktif · coverage ' + esc(t.coverage || '-') + '</p></div>';
                }).join('');
            })
            .catch(function () { tBox.innerHTML = '<p class="sn-help">Daftar wilayah gagal dimuat.</p>'; });
        fetchJSON(withParams('/outlets'))
            .then(function (json) {
                var rows = (json.data || []).slice(0, 10);
                if (!rows.length) { oBox.innerHTML = '<p class="sn-help">Belum ada titik outlet.</p>'; return; }
                oBox.innerHTML = rows.map(function (o) {
                    var link = (o.lat != null && o.lng != null)
                        ? ' <a class="underline font-bold" target="_blank" rel="noopener" href="https://www.google.com/maps?q=' + encodeURIComponent(o.lat + ',' + o.lng) + '">Buka Maps</a>'
                        : '';
                    return '<div class="border-2 border-black rounded-md px-3 py-2 bg-white">' +
                        '<p class="font-bold">' + esc(o.name) + '</p>' +
                        '<p class="text-xs text-neutral-500">' + esc(o.city || '') + ' · ' + esc(o.status || '') + link + '</p></div>';
                }).join('');
            })
            .catch(function () { oBox.innerHTML = '<p class="sn-help">Daftar outlet gagal dimuat.</p>'; });
    }

    /* ---------- drawer ---------- */
    function openDrawer(title, html) {
        qs('sn-drawer-title').textContent = title;
        qs('sn-drawer-body').innerHTML = html;
        qs('sn-map-drawer').classList.remove('hidden');
    }
    function getInfoWin() {
        if (!state.infoWin && window.google && window.google.maps) {
            state.infoWin = new window.google.maps.InfoWindow();
        }
        return state.infoWin;
    }
    function defaultPos() {
        return { lat: Number(CENTER.lat), lng: Number(CENTER.lng) };
    }
    function outletDrawerHtml(o) {
        var dir = (o.lat != null && o.lng != null) ? '<p><a class="sn-btn sn-btn-tosca sn-btn-sm" target="_blank" rel="noopener" href="' + gmapsDir(o.lat, o.lng) + '">Rute ke outlet</a></p>' : '';
        var photo = o.photo_url ? '<p><img src="' + esc(o.photo_url) + '" alt="Foto ' + esc(o.name || 'outlet') + '" class="w-full max-h-40 object-contain bg-white border-2 border-black rounded-sm" loading="lazy"></p>' : '';
        return photo +
            '<p><strong>Distributor:</strong> ' + esc(o.distributor || '—') + '</p>' +
            '<p><strong>Kota:</strong> ' + esc(o.city || '—') + ' · <strong>Kecamatan:</strong> ' + esc(o.district || '—') + '</p>' +
            '<p><strong>Status:</strong> ' + esc(o.status || '—') + '</p>' +
            '<p><strong>Alamat:</strong> ' + esc(o.address || '—') + '</p>' +
            '<p><strong>Telepon:</strong> ' + esc(o.phone || '—') + '</p>' + dir;
    }
    function openOutletDrawer(o) {
        openDrawer(o.name || 'Outlet', outletDrawerHtml(o));
    }
    window.snShowOutletDetail = function (id) {
        var o = state.outletById[id];
        if (o) openOutletDrawer(o);
    };
    function outletInfoHtml(o) {
        var photo = o.photo_url ? '<img src="' + esc(o.photo_url) + '" alt="Foto ' + esc(o.name || 'outlet') + '" style="width:120px;height:80px;object-fit:contain;background:#fff;border:2px solid #141414;border-radius:4px;" loading="lazy">' : '';
        return '<div style="max-width:240px;font-size:12px;line-height:1.4;">' +
            '<p style="font-weight:800;">' + esc(o.name || 'Outlet') + '</p>' +
            '<p>Distributor: ' + esc(o.distributor || '—') + '</p>' +
            '<p>' + esc(o.city || '—') + ' · Kec. ' + esc(o.district || '—') + ' · ' + esc(o.status || '—') + '</p>' +
            '<p>' + esc(o.address || '—') + '</p>' +
            '<p>Telp: ' + esc(o.phone || '—') + '</p>' +
            (photo ? '<p style="margin:4px 0;">' + photo + '</p>' : '') +
            '<p><button type="button" onclick="window.snShowOutletDetail(' + Number(o.id) + ')" style="border:2px solid #141414;border-radius:4px;padding:2px 8px;font-weight:800;background:#0BBCD6;color:#000;">Lihat Detail</button></p>' +
            '</div>';
    }
    function openOutletInfo(o, marker) {
        if (!state.mapReady) { openOutletDrawer(o); return; }
        var pos = { lat: Number(o.lat), lng: Number(o.lng) };
        state.map.panTo(pos);
        state.map.setZoom(15);
        var win = getInfoWin();
        if (win && marker) {
            win.setContent(outletInfoHtml(o));
            win.open(state.map, marker);
        }
        openOutletDrawer(o);
    }
    function fitOutletMarkers() {
        if (!state.mapReady) return;
        var pts = state.outletMarkers.map(function (m) { return m.getPosition(); }).filter(Boolean);
        if (!pts.length) {
            state.map.setCenter(defaultPos());
            state.map.setZoom(Number(CENTER.zoom) || 6);
            return;
        }
        if (pts.length === 1) {
            state.map.setCenter({ lat: pts[0].lat(), lng: pts[0].lng() });
            state.map.setZoom(15);
            return;
        }
        var bounds = new window.google.maps.LatLngBounds();
        pts.forEach(function (p) { bounds.extend(p); });
        state.map.fitBounds(bounds);
    }
    qs('sn-drawer-close').addEventListener('click', function () {
        qs('sn-map-drawer').classList.add('hidden');
    });

    function territoryDrawer(t) {
        var lat = t.lat != null ? t.lat : (t.center_lat != null ? t.center_lat : null);
        fetchJSON(API + '/activity?territory_id=' + encodeURIComponent(t.id))
            .then(function (a) {
                var visits = ((a.data || {}).visits || []).length;
                var txs = ((a.data || {}).transactions || []);
                var total = txs.reduce(function (s, x) { return s + (x.total || 0); }, 0);
                openDrawer((t.city || '') + ' – ' + (t.district || ''), drawerHtml(t, visits, txs.length, total, lat));
            })
            .catch(function () { openDrawer((t.city || '') + ' – ' + (t.district || ''), drawerHtml(t, '—', '—', '—', lat)); });

        function drawerHtml(t, visits, txCount, total, lat) {
            var lng = t.lng != null ? t.lng : t.center_lng;
            var dir = (lat != null && lng != null) ? '<p><a class="sn-btn sn-btn-tosca sn-btn-sm" target="_blank" rel="noopener" href="' + gmapsDir(lat, lng) + '">Rute ke wilayah</a></p>' : '';
            return '<p><strong>Kota:</strong> ' + esc(t.city || '—') + '</p>' +
                '<p><strong>Kecamatan:</strong> ' + esc(t.district || '—') + '</p>' +
                '<p><strong>Kode:</strong> <span class="font-mono">' + esc(t.code || '—') + '</span> · <strong>Status:</strong> ' + esc(t.status || '—') + '</p>' +
                '<p><strong>Distributor:</strong> ' + (t.distributors_count || 0) + ' · <strong>Outlet:</strong> ' + (t.outlets_count || 0) + ' (' + (t.active_outlets_count || 0) + ' aktif)</p>' +
                '<p><strong>Transaksi:</strong> ' + txCount + ' · <strong>Kunjungan:</strong> ' + visits + '</p>' +
                '<p><strong>Coverage:</strong> ' + esc(t.coverage || '-') + '</p>' +
                '<p><a class="sn-btn sn-btn-ghost sn-btn-sm" href="/developer/wilayah/' + encodeURIComponent(t.id) + '">Lihat detail wilayah</a></p>' + dir;
        }
    }

    /* ---------- Google Maps ---------- */
    var OUTLET_COLORS = { active: '#0FA3A3', inactive: '#6b7280', pending: '#FFD21F' };
    var DIST_COLORS = { approved: '#16a34a', pending: '#FFD21F', rejected: '#e11d48', suspended: '#6b7280' };
    var COV_COLORS = { covered: '#0FA3A3', partial: '#FFD21F', uncovered: '#e11d48' };

    function dotIcon(color) {
        return {
            path: window.google.maps.SymbolPath.CIRCLE,
            fillColor: color, fillOpacity: 1,
            strokeColor: '#141414', strokeWeight: 2,
            scale: 9,
        };
    }
    function clearMarkers(list) {
        list.forEach(function (m) { m.setMap(null); });
        list.length = 0;
    }

    function loadTerritories() {
        fetchJSON(withParams('/territories'))
            .then(function (json) {
                state.territories = json.data || [];
                state.territoryShapes.forEach(function (s) { s.setMap(null); });
                state.territoryShapes = [];
                if (!state.mapReady) return;
                state.territories.slice(0, 50).forEach(function (t) {
                    var lat = t.lat != null ? Number(t.lat) : (t.center_lat != null ? Number(t.center_lat) : NaN);
                    var lng = t.lng != null ? Number(t.lng) : (t.center_lng != null ? Number(t.center_lng) : NaN);
                    if (!isFinite(lat) || !isFinite(lng)) return;
                    var color = state.layers.coverage ? (COV_COLORS[t.coverage] || '#6b7280') : '#0BBCD6';
                    var circle = new window.google.maps.Circle({
                        map: state.layers.territory ? state.map : null,
                        center: { lat: lat, lng: lng },
                        radius: 8000 + Math.min(40000, (t.outlets_count || 0) * 3000),
                        fillColor: color, fillOpacity: 0.35,
                        strokeColor: '#141414', strokeWeight: 2,
                    });
                    circle.addListener('click', function () { territoryDrawer(t); });
                    state.territoryShapes.push(circle);
                });
            })
            .catch(function () {});
    }

    function loadDistributors() {
        var p = {};
        if (state.filters.distributor_status) p.distributor_status = state.filters.distributor_status;
        if (state.filters.city) p.city = state.filters.city;
        fetchJSON(withParams('/distributors', p))
            .then(function (json) {
                clearMarkers(state.distributorMarkers);
                if (!state.mapReady) return;
                (json.data || []).slice(0, 50).forEach(function (d) {
                    if (d.lat == null || d.lng == null) return;
                    var m = new window.google.maps.Marker({
                        position: { lat: Number(d.lat), lng: Number(d.lng) },
                        map: state.layers.distributor ? state.map : null,
                        title: d.name || '',
                        icon: dotIcon(DIST_COLORS[d.status] || '#0BBCD6'),
                    });
                    m.addListener('click', function () {
                        var dir = '<p><a class="sn-btn sn-btn-tosca sn-btn-sm" target="_blank" rel="noopener" href="' + gmapsDir(d.lat, d.lng) + '">Rute ke distributor</a></p>';
                        openDrawer(d.name || 'Distributor',
                            '<p><strong>Status:</strong> ' + esc(d.status || '—') + '</p>' +
                            '<p><strong>Wilayah:</strong> ' + esc((d.territory && (d.territory.city + ' – ' + d.territory.district)) || '—') + '</p>' +
                            '<p><strong>Outlet:</strong> ' + (d.outlets_count || 0) + '</p>' + dir);
                    });
                    state.distributorMarkers.push(m);
                });
            })
            .catch(function () {});
    }

    function loadOutletsInBounds() {
        var p = {};
        if (state.filters.city) p.city = state.filters.city;
        if (state.filters.district) p.district = state.filters.district;
        if (state.filters.outlet_status) p.outlet_status = state.filters.outlet_status;
        if (state.filters.min_volume) p.min_volume = state.filters.min_volume;
        if (state.filters.date_from) p.date_from = state.filters.date_from;
        if (state.filters.date_to) p.date_to = state.filters.date_to;
        if (state.mapReady && state.map) {
            var b = state.map.getBounds();
            if (b) {
                var ne = b.getNorthEast(), sw = b.getSouthWest();
                p.north = ne.lat(); p.south = sw.lat(); p.east = ne.lng(); p.west = sw.lng();
            }
        }
        fetchJSON(withParams('/outlets', p))
            .then(function (json) {
                var rows = json.data || [];
                // Clustering sederhana: selalu berbasis bbox viewport; bila >200 marker,
                // zoom-out otomatis tidak memuat semua — data dibatasi server (500) per viewport.
                clearMarkers(state.outletMarkers);
                state.outletById = {};
                if (!state.mapReady) return;
                rows.forEach(function (o) {
                    if (o.lat == null || o.lng == null) return;
                    state.outletById[o.id] = o;
                    var m = new window.google.maps.Marker({
                        position: { lat: Number(o.lat), lng: Number(o.lng) },
                        map: state.layers.outlet ? state.map : null,
                        title: o.name || '',
                        icon: dotIcon(OUTLET_COLORS[o.status] || '#0BBCD6'),
                    });
                    m.addListener('click', function () {
                        var win = getInfoWin();
                        if (win) {
                            win.setContent(outletInfoHtml(o));
                            win.open(state.map, m);
                        }
                        openOutletDrawer(o);
                    });
                    m._outletId = o.id;
                    state.outletMarkers.push(m);
                });
                if (state.fitNext) {
                    state.fitNext = false;
                    fitOutletMarkers();
                }
            })
            .catch(function () {});
    }

    function loadActivity() {
        if (!state.layers.activity || !state.mapReady) { clearMarkers(state.activityMarkers); return; }
        var p = {};
        if (state.filters.date_from) p.date_from = state.filters.date_from;
        if (state.filters.date_to) p.date_to = state.filters.date_to;
        fetchJSON(withParams('/activity', p))
            .then(function (json) {
                clearMarkers(state.activityMarkers);
                var d = json.data || {};
                (d.visits || []).forEach(function (v) {
                    if (v.lat == null || v.lng == null) return;
                    state.activityMarkers.push(new window.google.maps.Marker({
                        position: { lat: Number(v.lat), lng: Number(v.lng) },
                        map: state.map, title: 'Kunjungan: ' + (v.outlet || ''),
                        icon: dotIcon('#FFD21F'),
                    }));
                });
                (d.transactions || []).forEach(function (t) {
                    if (t.lat == null || t.lng == null) return;
                    state.activityMarkers.push(new window.google.maps.Marker({
                        position: { lat: Number(t.lat), lng: Number(t.lng) },
                        map: state.map, title: 'Transaksi: ' + (t.outlet || ''),
                        icon: dotIcon('#16a34a'),
                    }));
                });
            })
            .catch(function () {});
    }

    function refreshAll() {
        loadStats();
        renderLists();
        loadTerritories();
        loadDistributors();
        loadOutletsInBounds();
        loadActivity();
    }

    /* ---------- search ---------- */
    function doSearch() {
        var q = qs('sn-search').value.trim();
        var box = qs('sn-search-results');
        if (q.length < 2) { box.innerHTML = '<p class="sn-help">Minimal 2 huruf.</p>'; return; }
        box.innerHTML = '<p class="sn-help">Mencari…</p>';
        fetchJSON(API + '/search?q=' + encodeURIComponent(q))
            .then(function (json) {
                var d = json.data || {};
                var html = '';
                (d.territories || []).forEach(function (t, i) {
                    html += '<button type="button" class="sn-btn sn-btn-ghost sn-btn-sm sn-btn-block" data-s="t' + i + '">' + esc(t.city) + ' – ' + esc(t.district) + ' (wilayah)</button>';
                });
                (d.distributors || []).forEach(function (x, i) {
                    html += '<button type="button" class="sn-btn sn-btn-ghost sn-btn-sm sn-btn-block" data-s="d' + i + '">' + esc(x.name) + ' (distributor)</button>';
                });
                (d.outlets || []).forEach(function (o, i) {
                    html += '<button type="button" class="sn-btn sn-btn-ghost sn-btn-sm sn-btn-block" data-s="o' + i + '">' + esc(o.name) + ' (outlet)</button>';
                });
                box.innerHTML = html || '<p class="sn-help">Tidak ditemukan.</p>';
                box.querySelectorAll('button').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var k = btn.getAttribute('data-s');
                        var type = k.charAt(0), idx = parseInt(k.slice(1), 10);
                        if (type === 't') {
                            var t = (d.territories || [])[idx];
                            if (state.mapReady && t.lat != null) { state.map.setCenter({ lat: Number(t.lat), lng: Number(t.lng) }); state.map.setZoom(11); }
                            territoryDrawer(t);
                        } else if (type === 'd') {
                            var x = (d.distributors || [])[idx];
                            if (state.mapReady && x.lat != null) { state.map.setCenter({ lat: Number(x.lat), lng: Number(x.lng) }); state.map.setZoom(13); }
                            openDrawer(x.name || 'Distributor', '<p><strong>Status:</strong> ' + esc(x.status || '—') + '</p><p><a class="sn-btn sn-btn-tosca sn-btn-sm" target="_blank" rel="noopener" href="' + gmapsDir(x.lat, x.lng) + '">Rute</a></p>');
                        } else {
                            var o = (d.outlets || [])[idx];
                            state.outletById[o.id] = Object.assign({}, state.outletById[o.id] || {}, o);
                            var full = state.outletById[o.id];
                            if (state.mapReady && o.lat != null && o.lng != null) {
                                state.map.setCenter({ lat: Number(o.lat), lng: Number(o.lng) });
                                state.map.setZoom(15);
                                var mk = null;
                                state.outletMarkers.forEach(function (m) { if (m._outletId === o.id) mk = m; });
                                var win = getInfoWin();
                                if (win) {
                                    win.setContent(outletInfoHtml(full));
                                    if (mk) { win.open(state.map, mk); }
                                    else { win.setPosition({ lat: Number(o.lat), lng: Number(o.lng) }); win.open(state.map); }
                                }
                            }
                            openOutletDrawer(full);
                        }
                    });
                });
            })
            .catch(function () { box.innerHTML = '<p class="sn-help">Pencarian gagal.</p>'; });
    }
    qs('sn-search-btn').addEventListener('click', doSearch);
    qs('sn-search').addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); doSearch(); } });

    /* ---------- layer & filter ---------- */
    document.querySelectorAll('[data-layer]').forEach(function (cb) {
        cb.addEventListener('change', function () {
            state.layers[cb.getAttribute('data-layer')] = cb.checked;
            if (!state.mapReady) return;
            if (cb.getAttribute('data-layer') === 'territory' || cb.getAttribute('data-layer') === 'coverage') { loadTerritories(); return; }
            if (cb.getAttribute('data-layer') === 'distributor') { state.distributorMarkers.forEach(function (m) { m.setMap(cb.checked ? state.map : null); }); return; }
            if (cb.getAttribute('data-layer') === 'outlet') { state.outletMarkers.forEach(function (m) { m.setMap(cb.checked ? state.map : null); }); return; }
            if (cb.getAttribute('data-layer') === 'activity') { loadActivity(); }
        });
    });
    qs('sn-filter-btn').addEventListener('click', function () {
        state.filters = {
            city: qs('f-city').value.trim(),
            district: qs('f-district').value.trim(),
            outlet_status: qs('f-outlet-status').value,
            distributor_status: qs('f-dist-status').value,
            date_from: qs('f-date-from').value,
            date_to: qs('f-date-to').value,
            min_volume: qs('f-volume').value,
        };
        state.fitNext = true;
        refreshAll();
    });

    function showFallback() {
        qs('sn-map-fallback').classList.remove('hidden');
    }

    /* ---------- boot ---------- */
    loadStats();
    renderLists();

    if (!MAPS_KEY) { showFallback(); return; }

    var timedOut = false;
    var timer = setTimeout(function () {
        if (!state.mapReady) { timedOut = true; showFallback(); }
    }, 12000);

    window.snMapReady = function () {
        if (timedOut) return;
        clearTimeout(timer);
        try {
            state.map = new window.google.maps.Map(qs('sn-map'), {
                center: { lat: Number(CENTER.lat), lng: Number(CENTER.lng) },
                zoom: Number(CENTER.zoom) || 6,
            });
            state.mapReady = true;
            loadTerritories();
            loadDistributors();
            loadOutletsInBounds();
            state.map.addListener('idle', function () {
                if (state.bboxTimer) clearTimeout(state.bboxTimer);
                state.bboxTimer = setTimeout(loadOutletsInBounds, 400);
            });
        } catch (err) { showFallback(); }
    };

    var s = document.createElement('script');
    s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(MAPS_KEY) + '&callback=snMapReady';
    s.async = true; s.defer = true;
    s.onerror = function () { clearTimeout(timer); showFallback(); };
    document.head.appendChild(s);
})();
</script>
@endpush
