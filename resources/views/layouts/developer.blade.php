<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Command Center — SENYUM')</title>
    <meta name="description" content="@yield('meta_description', 'SENYUM Command Center untuk developer.')">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0FA3A3">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="min-h-screen bg-[#FFF9EC]">
    <a href="#dev-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[110] focus:bg-[#0FA3A3] focus:text-black focus:px-4 focus:py-2 focus:border-2 focus:border-black">
        Lewati ke konten
    </a>

    @php
        $devUser = auth()->user();
        $isActive = fn ($patterns) => request()->routeIs(...(array) $patterns) ? 'is-active' : '';
    @endphp

    {{-- Topbar tosca (mobile) --}}
    <header class="lg:hidden sticky top-0 z-50 border-b-[3px] border-black" style="background:#0FA3A3;">
        <div class="px-4 h-16 flex items-center gap-3">
            <button type="button" id="dev-drawer-open" class="sn-btn sn-btn-sm" style="background:#fff;color:#000;" aria-expanded="false" aria-controls="dev-drawer" aria-label="Buka navigasi Command Center">
                &#9776;
            </button>
            <a href="{{ route('developer.dashboard') }}" class="flex items-center gap-2" aria-label="SENYUM Command Center — Beranda">
                <x-senyum-logo size="sm" />
                <span class="font-display font-black uppercase tracking-wide text-sm text-black">Senyum <span class="text-white">Command Center</span></span>
            </a>
        </div>
    </header>

    <div class="lg:grid lg:grid-cols-[264px_1fr] lg:min-h-screen">
        {{-- Sidebar kiri fixed (desktop) identitas TOSCA --}}
        <aside class="hidden lg:flex sn-sidebar lg:sticky lg:top-0 lg:h-screen" aria-label="Navigasi Command Center">
            <div class="sn-sidebar-brand">
                <x-senyum-logo size="sm" />
                <div>
                    <p class="sn-sidebar-brand-name">Senyum<br>Command Center</p>
                    <p class="font-mono text-[10px] uppercase tracking-widest text-white/90 mt-1">Developer · Tosca</p>
                </div>
            </div>

            <nav class="py-2 overflow-y-auto" aria-label="Menu Command Center">
                <p class="sn-sidebar-group">Command Center</p>
                <a href="{{ route('developer.dashboard') }}" class="sn-side-link {{ $isActive('developer.dashboard') }}">Dashboard</a>

                <p class="sn-sidebar-group">Network</p>
                <a href="{{ route('developer.wilayah') }}" class="sn-side-link {{ $isActive(['developer.wilayah', 'developer.wilayah.*']) }}">Wilayah</a>
                <a href="{{ route('developer.distributor') }}" class="sn-side-link {{ $isActive(['developer.distributor', 'developer.distributor.*']) }}">Distributor</a>
                <a href="{{ route('developer.outlet') }}" class="sn-side-link {{ $isActive('developer.outlet*') }}">Outlet</a>
                <a href="{{ route('developer.peta') }}" class="sn-side-link {{ $isActive('developer.peta*') }}">Peta Strategis</a>

                <p class="sn-sidebar-group">Product</p>
                <a href="{{ route('developer.produk') }}" class="sn-side-link {{ $isActive('developer.produk*') }}">Produk</a>
                <a href="{{ route('developer.inventory') }}" class="sn-side-link {{ $isActive('developer.inventory*') }}">Inventory</a>

                <p class="sn-sidebar-group">Operations</p>
                <a href="{{ route('developer.po') }}" class="sn-side-link {{ $isActive('developer.po*') }}">Purchase Order</a>

                <p class="sn-sidebar-group">Analytics</p>
                <a href="{{ route('developer.laporan') }}" class="sn-side-link {{ $isActive('developer.laporan*') }}">Laporan</a>

                <p class="sn-sidebar-group">System</p>
                <a href="{{ route('developer.notifikasi') }}" class="sn-side-link {{ $isActive('developer.notifikasi*') }}">Notifikasi</a>
                <a href="{{ route('developer.profil') }}" class="sn-side-link {{ $isActive('developer.profil*') }}">Profil</a>
            </nav>

            <div class="sn-sidebar-foot">
                <p class="font-mono text-xs font-bold text-black truncate" title="{{ $devUser->name ?? 'Developer' }}">{{ $devUser->name ?? 'Developer' }}</p>
                <p class="font-mono text-[11px] text-black/70 truncate">{{ '@' . ($devUser->username ?? '—') }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="sn-btn sn-btn-sm sn-btn-block" style="background:#141414;color:#fff;" aria-label="Keluar dari Command Center">Logout</button>
                </form>
            </div>
        </aside>

        {{-- Drawer mobile --}}
        <div id="dev-drawer-backdrop" class="hidden fixed inset-0 z-[60] bg-black/60 lg:hidden" data-drawer-close></div>
        <div id="dev-drawer" class="hidden lg:hidden fixed inset-y-0 left-0 z-[61] w-72 sn-sidebar overflow-y-auto" role="dialog" aria-modal="true" aria-label="Navigasi Command Center seluler">
            <div class="sn-sidebar-brand">
                <x-senyum-logo size="sm" />
                <div class="flex-1">
                    <p class="sn-sidebar-brand-name">Senyum<br>Command Center</p>
                </div>
                <button type="button" class="sn-btn sn-btn-sm" style="background:#fff;color:#000;" data-drawer-close aria-label="Tutup navigasi">Tutup</button>
            </div>
            <nav class="py-2" aria-label="Menu Command Center seluler">
                <p class="sn-sidebar-group">Command Center</p>
                <a href="{{ route('developer.dashboard') }}" class="sn-side-link {{ $isActive('developer.dashboard') }}">Dashboard</a>
                <p class="sn-sidebar-group">Network</p>
                <a href="{{ route('developer.wilayah') }}" class="sn-side-link {{ $isActive(['developer.wilayah', 'developer.wilayah.*']) }}">Wilayah</a>
                <a href="{{ route('developer.distributor') }}" class="sn-side-link {{ $isActive(['developer.distributor', 'developer.distributor.*']) }}">Distributor</a>
                <a href="{{ route('developer.outlet') }}" class="sn-side-link {{ $isActive('developer.outlet*') }}">Outlet</a>
                <a href="{{ route('developer.peta') }}" class="sn-side-link {{ $isActive('developer.peta*') }}">Peta Strategis</a>
                <p class="sn-sidebar-group">Product</p>
                <a href="{{ route('developer.produk') }}" class="sn-side-link {{ $isActive('developer.produk*') }}">Produk</a>
                <a href="{{ route('developer.inventory') }}" class="sn-side-link {{ $isActive('developer.inventory*') }}">Inventory</a>
                <p class="sn-sidebar-group">Operations</p>
                <a href="{{ route('developer.po') }}" class="sn-side-link {{ $isActive('developer.po*') }}">Purchase Order</a>
                <p class="sn-sidebar-group">Analytics</p>
                <a href="{{ route('developer.laporan') }}" class="sn-side-link {{ $isActive('developer.laporan*') }}">Laporan</a>
                <p class="sn-sidebar-group">System</p>
                <a href="{{ route('developer.notifikasi') }}" class="sn-side-link {{ $isActive('developer.notifikasi*') }}">Notifikasi</a>
                <a href="{{ route('developer.profil') }}" class="sn-side-link {{ $isActive('developer.profil*') }}">Profil</a>
            </nav>
            <div class="sn-sidebar-foot">
                <p class="font-mono text-xs font-bold text-black truncate">{{ $devUser->name ?? 'Developer' }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="sn-btn sn-btn-sm sn-btn-block" style="background:#141414;color:#fff;">Logout</button>
                </form>
            </div>
        </div>

        <main id="dev-content" class="min-w-0 px-4 py-6 lg:px-8">
            <div class="max-w-6xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>

    @livewireScripts
    <script>
    (function () {
        var openBtn = document.getElementById('dev-drawer-open');
        var drawer = document.getElementById('dev-drawer');
        var backdrop = document.getElementById('dev-drawer-backdrop');
        function open() {
            if (!drawer) return;
            drawer.classList.remove('hidden');
            if (backdrop) backdrop.classList.remove('hidden');
            if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
        }
        function close() {
            if (!drawer) return;
            drawer.classList.add('hidden');
            if (backdrop) backdrop.classList.add('hidden');
            if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
        }
        if (openBtn) openBtn.addEventListener('click', open);
        document.querySelectorAll('[data-drawer-close]').forEach(function (el) {
            el.addEventListener('click', close);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') close();
        });
        // Modal brutalist generik: [data-modal-open="#id"] / [data-modal-close] / [data-modal-confirm]
        document.addEventListener('click', function (e) {
            var opener = e.target.closest('[data-modal-open]');
            if (opener) {
                var modal = document.querySelector(opener.getAttribute('data-modal-open'));
                if (modal) modal.classList.add('is-open');
                return;
            }
            if (e.target.closest('[data-modal-close]')) {
                var m1 = e.target.closest('.sn-modal-backdrop');
                if (m1) m1.classList.remove('is-open');
                return;
            }
            var confirmBtn = e.target.closest('[data-modal-confirm]');
            if (confirmBtn) {
                var m2 = confirmBtn.closest('.sn-modal-backdrop');
                var form = m2 ? m2.querySelector('form') : null;
                if (form) form.submit();
            }
            var alertClose = e.target.closest('[data-alert-close]');
            if (alertClose) {
                var alert = alertClose.closest('.sn-alert');
                if (alert) alert.remove();
            }
        });
    })();
    </script>
    @stack('scripts')
</body>
</html>
