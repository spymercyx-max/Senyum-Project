<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk — SENYUM')</title>
    <meta name="description" content="@yield('meta_description', 'Masuk ke akun SENYUM.')">
    <meta name="theme-color" content="#0BBCD6">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="min-h-screen sn-hero-grid">
    <a href="#auth-form" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-[110] focus:bg-[#FFD21F] focus:text-black focus:px-4 focus:py-2 focus:border-2 focus:border-black">
        Lewati ke formulir
    </a>

    <main class="min-h-screen grid lg:grid-cols-2">
        {{-- Brand panel: desktop only --}}
        <aside class="hidden lg:flex flex-col justify-between bg-[#0BBCD6] border-r-[3px] border-[#141414] p-10" aria-label="Panel merek SENYUM">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 font-display font-black uppercase tracking-wide text-[#062A30]" aria-label="Kembali ke beranda SENYUM">
                <x-senyum-logo size="md" />
                <span class="text-xl">Senyum</span>
            </a>
            <div>
                <p class="sn-kicker mb-4">Kretek Tangan Asli</p>
                <h1 class="font-display uppercase leading-[1.05] text-5xl text-[#141414]">Berani.<br>Jujur.<br>Khas.</h1>
                <p class="mt-4 max-w-md text-[#062A30]">Satu hisapan, satu senyum. Masuk untuk mengelola pesanan, outlet, dan kemitraanmu.</p>
                <div class="mt-8 inline-block bg-[#FFD21F] border-[3px] border-[#141414] shadow-[6px_6px_0_#141414] rounded px-4 py-3 font-mono text-xs font-bold uppercase tracking-widest">
                    Khusus 18+ — Merokok membahayakan kesehatan
                </div>
            </div>
            <p class="font-mono text-xs uppercase tracking-widest text-[#062A30]">&copy; <span data-year>{{ date('Y') }}</span> {{ config('app.name', 'SENYUM') }}</p>
        </aside>

        {{-- Form side --}}
        <section class="flex flex-col justify-center px-4 py-10 sm:px-10" aria-label="Formulir autentikasi">
            <div class="w-full max-w-md mx-auto">
                <div class="lg:hidden flex items-center justify-center mb-6">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2" aria-label="Kembali ke beranda SENYUM">
                        <x-senyum-logo size="md" />
                        <span class="font-display font-black uppercase tracking-wide text-lg">Senyum</span>
                    </a>
                </div>

                <div class="sn-card" id="auth-form">
                    @yield('content')
                </div>

                <p class="text-center mt-6">
                    <a href="{{ url('/') }}" class="sn-navlink">&larr; Kembali ke Beranda</a>
                </p>
                <p class="text-center mt-3 font-mono text-[11px] uppercase tracking-widest text-neutral-500">Khusus 18 tahun ke atas.</p>
            </div>
        </section>
    </main>

    @livewireScripts
    @stack('scripts')
</body>
</html>
