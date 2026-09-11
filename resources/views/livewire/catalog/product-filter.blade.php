<div>
    <div class="sn-card mb-6">
        <label class="sn-label" for="catalog-q">Cari produk</label>
        <input
            id="catalog-q"
            type="search"
            class="sn-input"
            placeholder="Ketik nama produk, mis. Original…"
            wire:model.live.debounce.300ms="q"
            autocomplete="off"
        >
        <p class="sn-help">Pencarian berjalan otomatis saat kamu mengetik.</p>
    </div>

    @if ($products->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" role="list">
            @foreach ($products as $product)
                <div role="listitem">
                    <x-senyum-product-card :product="$product" :key="$product->id" />
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @else
        <x-senyum-empty-state
            title="Produk tidak ditemukan"
            message="Coba kata kunci lain atau lihat seluruh katalog kami."
            action="Lihat Semua Produk"
            :href="route('products.index')"
        />
    @endif
</div>
