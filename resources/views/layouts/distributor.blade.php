<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Field Workspace — SENYUM')</title>
    <meta name="description" content="@yield('meta_description', 'SENYUM Field Workspace untuk distributor.')">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0BBCD6">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="min-h-screen bg-[#FFF9EC]">
    <a href="#field-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[110] focus:bg-[#FFD21F] focus:text-black focus:px-4 focus:py-2 focus:border-2 focus:border-black">
        Lewati ke konten
    </a>

    {{-- Mobile topbar --}}
    <header class="lg:hidden bg-[#0BBCD6] border-b-[3px] border-black sticky top-0 z-50">
        <div class="px-4 h-14 flex items-center gap-2">
            <button type="button" data-menu-btn data-menu-target="#dist-drawer" aria-expanded="false" aria-controls="dist-drawer" aria-label="Buka menu navigasi" class="sn-btn sn-btn-sm bg-white border-2 border-black rounded-sm px-2 py-1 font-black">
                ☰
            </button>
            <a href="{{ Route::has('distributor.dashboard') ? route('distributor.dashboard') : url('/distributor') }}" class="flex items-center gap-2" aria-label="SENYUM Field Workspace — Beranda">
                <x-senyum-logo size="sm" />
                <span class="font-display font-black uppercase tracking-wide text-sm text-black">Field Workspace</span>
            </a>
            <span class="ml-auto font-mono text-[10px] font-bold uppercase tracking-widest bg-black text-white rounded-sm px-2 py-1 truncate max-w-[40%]">{{ auth()->user()->name ?? 'Distributor' }}</span>
        </div>
    </header>

    <div class="lg:flex lg:min-h-screen">
        {{-- Sidebar / drawer --}}
        <aside id="dist-drawer" data-menu-panel class="hidden lg:flex fixed lg:sticky top-0 z-[60] lg:z-30 h-screen w-[272px] shrink-0 bg-white border-r-[3px] border-black flex-col overflow-y-auto" aria-label="Navigasi Field Workspace">
            <div class="bg-[#0BBCD6] border-b-[3px] border-black px-4 py-4">
                <div class="flex items-center gap-2">
                    <x-senyum-logo size="sm" />
                    <div class="min-w-0">
                        <p class="font-display font-black uppercase text-black leading-none">Field Workspace</p>
                        <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-black/80 mt-1">Kretak Senyum 2.0</p>
                    </div>
                    <button type="button" data-menu-btn data-menu-target="#dist-drawer" aria-expanded="true" aria-label="Tutup menu" class="lg:hidden ml-auto sn-btn sn-btn-sm bg-white border-2 border-black rounded-sm px-2 py-1 font-black">✕</button>
                </div>
                <div class="mt-3 bg-black text-white rounded-sm px-3 py-2">
                    <p class="font-bold text-sm truncate">{{ auth()->user()->name ?? 'Distributor' }}</p>
                    <p class="font-mono text-[11px] uppercase tracking-widest text-white/80 truncate">{{ $territory ?? auth()->user()?->distributorProfile?->territory?->displayName() ?? 'Wilayah belum dipasang' }}</p>
                </div>
            </div>

            @php
                $isSales = request()->routeIs('distributor.pemesanan*', 'distributor.order*', 'distributor.transaksi*', 'distributor.jual-ecer*');
                $isNetwork = request()->routeIs('distributor.outlet*', 'distributor.visit*', 'distributor.peta*');
                $isOps = request()->routeIs('distributor.po*', 'distributor.pembukuan*');
                $isSys = request()->routeIs('distributor.notifikasi*', 'distributor.profil*', 'distributor.bantuan*');
                $linkBase = 'flex items-center gap-2 px-3 py-2 border-2 border-black rounded-sm font-bold text-sm';
                $linkIdle = 'bg-white text-black hover:bg-[#FFD21F]';
                $linkActive = 'bg-black text-white';
            @endphp

            <nav class="p-3 space-y-4 text-sm">
                <a href="{{ route('distributor.dashboard') }}" class="{{ $linkBase }} {{ request()->routeIs('distributor.dashboard') ? $linkActive : 'bg-[#0BBCD6] text-black hover:bg-[#FFD21F]' }}" aria-current="{{ request()->routeIs('distributor.dashboard') ? 'page' : 'false' }}">
                    <span aria-hidden="true">⌂</span> Dashboard
                </a>

                <section aria-label="Sales">
                    <p class="sn-kicker mb-1.5">Sales</p>
                    <div class="grid gap-1.5">
                        <a href="{{ route('distributor.pemesanan') }}" class="{{ $linkBase }} {{ request()->routeIs('distributor.pemesanan*', 'distributor.order*') ? $linkActive : $linkIdle }}">Pemesanan</a>
                        <a href="{{ route('distributor.transaksi') }}" class="{{ $linkBase }} {{ request()->routeIs('distributor.transaksi*') ? $linkActive : $linkIdle }}">Transaksi</a>
                        <a href="{{ route('distributor.jual-ecer') }}" class="{{ $linkBase }} {{ request()->routeIs('distributor.jual-ecer*') ? $linkActive : $linkIdle }}">Jual Ecer</a>
                    </div>
                </section>

                <section aria-label="Network">
                    <p class="sn-kicker mb-1.5">Network</p>
                    <div class="grid gap-1.5">
                        <a href="{{ route('distributor.outlet') }}" class="{{ $linkBase }} {{ request()->routeIs('distributor.outlet*') ? $linkActive : $linkIdle }}">Outlet</a>
                        <a href="{{ route('distributor.visit') }}" class="{{ $linkBase }} {{ request()->routeIs('distributor.visit*') ? $linkActive : $linkIdle }}">Visit</a>
                        <a href="{{ route('distributor.peta') }}" class="{{ $linkBase }} {{ request()->routeIs('distributor.peta*') ? $linkActive : $linkIdle }}">Peta</a>
                    </div>
                </section>

                <section aria-label="Operations">
                    <p class="sn-kicker mb-1.5">Operations</p>
                    <div class="grid gap-1.5">
                        <a href="{{ route('distributor.po') }}" class="{{ $linkBase }} {{ request()->routeIs('distributor.po*') ? $linkActive : $linkIdle }}">PO</a>
                        <a href="{{ route('distributor.pembukuan') }}" class="{{ $linkBase }} {{ request()->routeIs('distributor.pembukuan*') ? $linkActive : $linkIdle }}">Pembukuan</a>
                    </div>
                </section>

                <section aria-label="System">
                    <p class="sn-kicker mb-1.5">System</p>
                    <div class="grid gap-1.5">
                        <a href="{{ route('distributor.notifikasi') }}" class="{{ $linkBase }} {{ request()->routeIs('distributor.notifikasi*') ? $linkActive : $linkIdle }}">Notifikasi</a>
                        <a href="{{ route('distributor.profil') }}" class="{{ $linkBase }} {{ request()->routeIs('distributor.profil*') ? $linkActive : $linkIdle }}">Profil</a>
                        <a href="{{ route('distributor.bantuan') }}" class="{{ $linkBase }} {{ request()->routeIs('distributor.bantuan*') ? $linkActive : $linkIdle }}">Bantuan</a>
                    </div>
                </section>
            </nav>

            <div class="mt-auto p-3 border-t-[3px] border-black">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sn-btn sn-btn-ghost sn-btn-sm w-full" aria-label="Keluar dari Field Workspace">Logout</button>
                </form>
                <p class="font-mono text-[10px] uppercase tracking-widest text-neutral-500 mt-2 text-center">CYAN · Field Workspace</p>
            </div>
        </aside>

        {{-- Content --}}
        <div class="flex-1 min-w-0">
            <div class="max-w-5xl mx-auto px-4 py-6 pb-16 lg:pb-10">
                <main id="field-content" class="min-w-0">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
