<div>
    @if ($notice)
        <x-senyum-alert type="success" :message="$notice" class="mb-4" />
    @endif
    @if ($error)
        <x-senyum-alert type="danger" :message="$error" class="mb-4" />
    @endif

    <div class="grid sm:grid-cols-2 gap-3 mb-4">
        <div>
            <label class="sn-label" for="pm-search">Cari produk</label>
            <input id="pm-search" type="search" wire:model.live="search" class="sn-input" placeholder="Nama atau SKU…" autocomplete="off">
        </div>
        <div>
            <label class="sn-label" for="pm-status">Status</label>
            <select id="pm-status" wire:model.live="status" class="sn-select">
                <option value="">Semua status</option>
                <option value="draft">Draf</option>
                <option value="active">Aktif</option>
                <option value="archived">Diarsipkan</option>
            </select>
        </div>
    </div>

    @if ($products->isEmpty())
        <x-senyum-empty-state title="Produk tidak ditemukan" message="Coba ubah kata kunci atau filter status." />
    @else
        <div class="sn-table-wrap hidden md:block">
            <table class="sn-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>SKU</th>
                        <th>Harga Customer</th>
                        <th>Harga Distributor</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        @php
                            $custTiers = $product->tiers->where('channel', 'customer')->sortBy('min_qty')->values();
                            $distTiers = $product->tiers->where('channel', 'distributor')->sortBy('min_qty')->values();
                        @endphp
                        <tr>
                            <td>
                                <p class="font-bold">{{ $product->name }}</p>
                                <p class="font-mono text-xs text-neutral-500">{{ $product->slug }}</p>
                                @if ($product->featured)
                                    <x-senyum-badge status="featured" class="mt-1">Unggulan</x-senyum-badge>
                                @endif
                            </td>
                            <td class="font-mono text-xs">{{ $product->sku }}</td>
                            <td class="text-xs">
                                <p class="font-black uppercase text-xs">Harga Customer</p>
                                <p class="text-neutral-600">Harga untuk pemesanan publik melalui website.</p>
                                <p class="sn-num mt-1">Rp{{ number_format($product->price, 0, ',', '.') }}</p>
                                @if ($custTiers->isEmpty())
                                    <p class="font-mono text-neutral-500 mt-1">—</p>
                                @else
                                    <ul class="font-mono mt-1 space-y-0.5">
                                        @foreach ($custTiers as $t)
                                            <li>{{ $t->min_qty }}{{ $t->max_qty ? '–' . $t->max_qty : '+' }} → Rp{{ number_format($t->price, 0, ',', '.') }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td class="text-xs">
                                <p class="font-black uppercase text-xs">Harga Distributor</p>
                                <p class="text-neutral-600">Harga pembelian Distributor dari Developer.</p>
                                <p class="sn-num mt-1">Rp{{ number_format($product->distributor_price, 0, ',', '.') }}</p>
                                @if ($distTiers->isEmpty())
                                    <p class="font-mono text-neutral-500 mt-1">—</p>
                                @else
                                    <ul class="font-mono mt-1 space-y-0.5">
                                        @foreach ($distTiers as $t)
                                            <li>{{ $t->min_qty }}{{ $t->max_qty ? '–' . $t->max_qty : '+' }} → Rp{{ number_format($t->price, 0, ',', '.') }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                <p class="text-neutral-500 mt-1">HARGA JUAL KE OUTLET ditentukan Distributor pada transaksi.</p>
                            </td>
                            <td class="sn-num">{{ $product->inventory?->available ?? 0 }} pcs</td>
                            <td><x-senyum-badge status="{{ $product->status }}">{{ strtoupper($product->status) }}</x-senyum-badge></td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    <a href="{{ route('developer.produk.edit', $product) }}" class="sn-btn sn-btn-ghost sn-btn-sm">EDIT</a>
                                    @if (! $product->featured && $product->status !== 'archived')
                                        <button type="button" wire:click="setFeatured({{ $product->id }})" wire:confirm="Jadikan produk {{ $product->name }} sebagai produk unggulan?" class="sn-btn sn-btn-tosca sn-btn-sm">✓ Jadikan Produk Unggulan</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-xs text-neutral-600 mt-2">HARGA JUAL KE OUTLET ditentukan Distributor pada transaksi.</p>
        </div>

        <div class="space-y-3 md:hidden">
            @foreach ($products as $product)
                @php
                    $custTiersM = $product->tiers->where('channel', 'customer')->sortBy('min_qty')->values();
                    $distTiersM = $product->tiers->where('channel', 'distributor')->sortBy('min_qty')->values();
                @endphp
                <div class="sn-card">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-display font-bold uppercase text-sm">{{ $product->name }}</p>
                        @if ($product->featured)
                            <x-senyum-badge status="featured">Unggulan</x-senyum-badge>
                        @endif
                        <x-senyum-badge status="{{ $product->status }}">{{ strtoupper($product->status) }}</x-senyum-badge>
                    </div>
                    <p class="font-mono text-xs text-neutral-500 mt-1">{{ $product->sku }}</p>
                    <div class="mt-2 border-2 border-black rounded-sm p-2 bg-white">
                        <p class="font-black uppercase text-xs">Harga Customer</p>
                        <p class="text-xs text-neutral-600">Harga untuk pemesanan publik melalui website.</p>
                        <p class="text-xs mt-1">Dasar: <strong>Rp{{ number_format($product->price, 0, ',', '.') }}</strong></p>
                        @if ($custTiersM->isEmpty())
                            <p class="font-mono text-xs text-neutral-500 mt-1">—</p>
                        @else
                            <ul class="font-mono text-xs mt-1 space-y-0.5">
                                @foreach ($custTiersM as $t)
                                    <li>{{ $t->min_qty }}{{ $t->max_qty ? '–' . $t->max_qty : '+' }} → Rp{{ number_format($t->price, 0, ',', '.') }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="mt-2 border-2 border-black rounded-sm p-2 bg-white">
                        <p class="font-black uppercase text-xs">Harga Distributor</p>
                        <p class="text-xs text-neutral-600">Harga pembelian Distributor dari Developer.</p>
                        <p class="text-xs mt-1">Dasar: <strong>Rp{{ number_format($product->distributor_price, 0, ',', '.') }}</strong></p>
                        @if ($distTiersM->isEmpty())
                            <p class="font-mono text-xs text-neutral-500 mt-1">—</p>
                        @else
                            <ul class="font-mono text-xs mt-1 space-y-0.5">
                                @foreach ($distTiersM as $t)
                                    <li>{{ $t->min_qty }}{{ $t->max_qty ? '–' . $t->max_qty : '+' }} → Rp{{ number_format($t->price, 0, ',', '.') }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <p class="text-xs text-neutral-600 mt-2">HARGA JUAL KE OUTLET ditentukan Distributor pada transaksi.</p>
                    <p class="text-xs mt-1">Stok tersedia: <strong>{{ $product->inventory?->available ?? 0 }} pcs</strong></p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <a href="{{ route('developer.produk.edit', $product) }}" class="sn-btn sn-btn-ghost sn-btn-sm">EDIT</a>
                        @if (! $product->featured && $product->status !== 'archived')
                            <button type="button" wire:click="setFeatured({{ $product->id }})" wire:confirm="Jadikan produk {{ $product->name }} sebagai produk unggulan?" class="sn-btn sn-btn-tosca sn-btn-sm">✓ Jadikan Produk Unggulan</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $products->links() }}</div>
    @endif
</div>
