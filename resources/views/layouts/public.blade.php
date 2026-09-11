<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SENYUM — Kretek Tangan Asli')</title>
    <meta name="description" content="@yield('meta_description', 'Kretek tangan SENYUM — racikan tembakau pilihan, cengkeh asli, dan kemitraan warung terpercaya.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('og_title', 'SENYUM — Kretek Tangan Asli')">
    <meta property="og:description" content="@yield('og_description', 'Kretek tangan SENYUM — berani, jujur, dan khas.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="theme-color" content="#0BBCD6">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col">
    @php
        $waNumber = config('senyum.whatsapp_number', '6281234567890');
        $waLink = 'https://wa.me/' . preg_replace('/[^0-9]/', '', (string) $waNumber);
    @endphp

    {{-- Smoke intro: hanya render jika belum pernah lihat (JS yang memutuskan, hormati reduced-motion) --}}
    <div id="sn-intro" role="presentation" aria-hidden="true">
        <canvas aria-hidden="true"></canvas>
        <div class="sn-intro-word">SENYUM</div>
        <p class="relative z-[1] font-mono text-xs tracking-widest text-white uppercase">Kretek Tangan Asli</p>
    </div>

    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[110] focus:bg-[#FFD21F] focus:text-black focus:px-4 focus:py-2 focus:border-2 focus:border-black">
        Lewati ke konten
    </a>

    <header class="border-b-[3px] border-[#141414] bg-white sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16 gap-4">
            <a href="{{ url('/') }}" class="flex items-center gap-2" aria-label="SENYUM — Beranda">
                <x-senyum-logo size="sm" />
                <span class="font-display font-black tracking-wide text-lg uppercase">Senyum</span>
            </a>

            <nav class="hidden md:flex items-center gap-1" aria-label="Navigasi utama">
                <a href="{{ url('/') }}" class="sn-navlink {{ request()->is('/') ? 'is-active' : '' }}">Home</a>
                <a href="{{ url('/tentang') }}" class="sn-navlink {{ request()->is('tentang*') ? 'is-active' : '' }}">Tentang</a>
                <a href="{{ url('/produk') }}" class="sn-navlink {{ request()->is('produk*') ? 'is-active' : '' }}">Produk</a>
                <a href="{{ url('/kemitraan') }}" class="sn-navlink {{ request()->is('kemitraan*') ? 'is-active' : '' }}">Kemitraan</a>
            </nav>

            <div class="hidden md:flex items-center gap-2">
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="sn-btn sn-btn-ghost sn-btn-sm">Login</a>
                @endif
                <a href="{{ $waLink }}" target="_blank" rel="noopener" class="sn-btn sn-btn-yellow sn-btn-sm" aria-label="Pesan via WhatsApp">Pesan</a>
            </div>

            <button type="button" class="sn-btn sn-btn-ghost sn-btn-sm md:hidden" data-menu-btn data-menu-target="#mobile-menu" aria-expanded="false" aria-controls="mobile-menu" aria-label="Buka menu navigasi">
                Menu
            </button>
        </div>

        <div id="mobile-menu" data-menu-panel class="hidden md:hidden border-t-[3px] border-[#141414] bg-[#FFF9EC] px-4 py-4" aria-label="Menu navigasi seluler">
            <nav class="flex flex-col gap-2" aria-label="Navigasi seluler">
                <a href="{{ url('/') }}" class="sn-navlink {{ request()->is('/') ? 'is-active' : '' }}">Home</a>
                <a href="{{ url('/tentang') }}" class="sn-navlink {{ request()->is('tentang*') ? 'is-active' : '' }}">Tentang</a>
                <a href="{{ url('/produk') }}" class="sn-navlink {{ request()->is('produk*') ? 'is-active' : '' }}">Produk</a>
                <a href="{{ url('/kemitraan') }}" class="sn-navlink {{ request()->is('kemitraan*') ? 'is-active' : '' }}">Kemitraan</a>
                <div class="flex gap-2 pt-2">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="sn-btn sn-btn-ghost sn-btn-sm flex-1">Login</a>
                    @endif
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="sn-btn sn-btn-yellow sn-btn-sm flex-1">Pesan</a>
                </div>
            </nav>
        </div>
    </header>

    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t-[3px] border-[#141414] bg-[#141414] text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <x-senyum-logo size="sm" />
                    <span class="font-display font-black uppercase tracking-wide">Senyum</span>
                </div>
                <p class="text-sm text-neutral-300 leading-relaxed">Kretek tangan asli — racikan berani, rasa jujur, harga merakyat.</p>
                <p class="mt-3 inline-block font-mono text-xs font-bold uppercase tracking-widest border-2 border-[#FFD21F] text-[#FFD21F] px-2 py-1">18+ Saja</p>
            </div>
            <nav aria-label="Navigasi footer">
                <h2 class="font-display uppercase text-sm mb-3 text-[#FFD21F]">Navigasi</h2>
                <ul class="space-y-2 text-sm">
                    <li><a class="underline-offset-4 hover:underline" href="{{ url('/') }}">Home</a></li>
                    <li><a class="underline-offset-4 hover:underline" href="{{ url('/tentang') }}">Tentang</a></li>
                    <li><a class="underline-offset-4 hover:underline" href="{{ url('/produk') }}">Produk</a></li>
                    <li><a class="underline-offset-4 hover:underline" href="{{ url('/kemitraan') }}">Kemitraan</a></li>
                </ul>
            </nav>
            <nav aria-label="Akun footer">
                <h2 class="font-display uppercase text-sm mb-3 text-[#FFD21F]">Akun</h2>
                <ul class="space-y-2 text-sm">
                    @if (Route::has('login'))
                        <li><a class="underline-offset-4 hover:underline" href="{{ route('login') }}">Login</a></li>
                    @endif
                    @if (Route::has('register'))
                        <li><a class="underline-offset-4 hover:underline" href="{{ route('register') }}">Register</a></li>
                    @endif
                </ul>
            </nav>
            <div>
                <h2 class="font-display uppercase text-sm mb-3 text-[#FFD21F]">Kontak</h2>
                <p class="text-sm text-neutral-300 mb-3">Pesan &amp; kemitraan langsung via WhatsApp resmi.</p>
                <a href="{{ $waLink }}" target="_blank" rel="noopener" class="sn-btn sn-btn-cyan sn-btn-sm" aria-label="Hubungi SENYUM via WhatsApp">WhatsApp</a>
            </div>
        </div>
        <div class="border-t-2 border-neutral-700">
            <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row gap-2 items-center justify-between text-xs font-mono uppercase tracking-widest text-neutral-400">
                <p>&copy; <span data-year>{{ date('Y') }}</span> {{ config('app.name', 'SENYUM') }}. Hak cipta dilindungi.</p>
                <p>Merokok membahayakan kesehatan. Khusus 18+.</p>
            </div>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
