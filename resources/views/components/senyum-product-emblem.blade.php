@props(['kicker' => 'Produk Unggulan', 'title' => 'Kretek Senyum Original', 'price' => null])

<div class="sn-pack-frame sn-featured" data-featured role="button" tabindex="0" aria-label="Produk unggulan {{ $title }} — ketuk untuk detail">
    <span class="sn-pack-corner" aria-hidden="true">
        <x-senyum-logo size="sm" />
    </span>
    <div class="sn-pack-frame-inner text-center">
        <p class="sn-kicker justify-center">{{ $kicker }}</p>
        <h3 class="font-display uppercase text-2xl sm:text-3xl mt-2">{{ $title }}</h3>
        @if ($price)
            <p class="font-mono font-bold mt-2">{{ $price }}</p>
        @endif
        <div class="sn-featured-detail mt-4 border-t-2 border-black pt-4 text-sm">
            {{ $slot }}
        </div>
        <p class="font-mono text-[11px] uppercase tracking-widest mt-3 text-neutral-500">Ketuk untuk detail</p>
    </div>
</div>
