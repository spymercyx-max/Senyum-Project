@props(['product' => null])

@php
    $waNumber = config('senyum.whatsapp_number', '6281234567890');
    $waLink = 'https://wa.me/' . preg_replace('/[^0-9]/', '', (string) $waNumber);

    $isArray = is_array($product);
    $name = $isArray ? ($product['name'] ?? 'Kretek Senyum') : ($product->name ?? 'Kretek Senyum');
    $price = $isArray ? ($product['price'] ?? null) : ($product->price ?? null);
    $image = $isArray ? ($product['image'] ?? null) : ($product->image ?? null);
    $slug = $isArray ? ($product['slug'] ?? '#') : ($product->slug ?? '#');
    $short = $isArray ? ($product['short_description'] ?? null) : ($product->short_description ?? null);
    $stockRaw = $isArray
        ? ($product['stock'] ?? 1)
        : ($product->stock ?? $product->inventory?->available ?? 0);
    $available = ((int) $stockRaw) > 0;

    // Rentang harga dari tier customer bila ada (tanpa data distributor).
    $customerTiers = collect();
    if (! $isArray && $product && method_exists($product, 'tiers')) {
        try {
            $customerTiers = $product->relationLoaded('tiers')
                ? $product->tiers->where('channel', 'customer')->sortBy('min_qty')->values()
                : $product->tiers()->where('channel', 'customer')->orderBy('min_qty')->get();
        } catch (\Throwable $e) {
            $customerTiers = collect();
        }
    }
    if ($customerTiers->count() > 0) {
        $minTier = (int) $customerTiers->min('price');
        $maxTier = (int) $customerTiers->max('price');
        $fmt = fn ($v) => 'Rp' . number_format((int) $v, 0, ',', '.');
        $priceLabel = $minTier === $maxTier ? $fmt($minTier) : $fmt($minTier) . ' – ' . $fmt($maxTier);
    } else {
        $priceLabel = $price !== null ? 'Rp' . number_format((int) $price, 0, ',', '.') : 'Harga hubungi admin';
    }
@endphp

<article class="sn-card !p-4 flex flex-col gap-3" aria-label="Produk {{ $name }}">
    <div class="border-2 border-black rounded bg-[#0BBCD6] relative overflow-hidden">
        <div class="aspect-[4/3] w-full flex items-center justify-center p-3">
            @if ($image)
                <img src="{{ $image }}" alt="Kemasan {{ $name }}" class="h-full w-full object-contain" loading="lazy">
            @else
                <div class="text-center" aria-hidden="true">
                    <x-senyum-logo size="md" />
                    <p class="font-mono text-[10px] font-bold uppercase tracking-widest mt-1 text-[#062A30]">Kemasan Senyum</p>
                </div>
            @endif
        </div>
        <span class="absolute top-2 right-2">
            <x-senyum-badge :status="$available ? 'active' : 'suspended'">{{ $available ? 'Tersedia' : 'Habis' }}</x-senyum-badge>
        </span>
    </div>

    <div>
        <h3 class="font-display uppercase text-base leading-tight">{{ $name }}</h3>
        @if ($short)
            <p class="text-sm leading-relaxed text-neutral-600 line-clamp-2 mt-1">{{ $short }}</p>
        @endif
        <p class="font-mono font-bold text-sm mt-2">{{ $priceLabel }}</p>
        @if ($customerTiers->count() > 1)
            <p class="font-mono text-[11px] uppercase tracking-widest text-neutral-500 mt-1">Harga bertingkat tersedia</p>
        @endif
    </div>

    <div class="flex gap-2 mt-auto">
        <a href="{{ url('/produk/' . $slug) }}" class="sn-btn sn-btn-ghost sn-btn-sm flex-1" aria-label="Lihat detail {{ $name }}">Detail</a>
        <a href="{{ $waLink }}" target="_blank" rel="noopener" class="sn-btn sn-btn-yellow sn-btn-sm flex-1" aria-label="Pesan {{ $name }} via WhatsApp">Pesan</a>
    </div>
</article>
