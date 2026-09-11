@extends('layouts.developer')

@section('title', ($mode === 'create' ? 'Tambah produk' : 'EDIT produk') . ' — SENYUM Command Center')

@section('content')
<div class="space-y-6 max-w-3xl">
    <div>
        <a href="{{ route('developer.produk') }}" class="sn-btn sn-btn-ghost sn-btn-sm mb-3">← Kembali ke produk</a>
        <p class="sn-kicker">Product · {{ $mode === 'create' ? 'Create' : 'EDIT' }}</p>
        <h1 class="font-display font-black uppercase text-2xl sm:text-3xl mt-1">{{ $mode === 'create' ? 'Tambah produk' : 'EDIT produk' }}</h1>
    </div>

    @if (session('success'))
        <x-senyum-alert type="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-senyum-alert type="danger" :message="session('error')" />
    @endif
    @if ($errors->any())
        <x-senyum-alert type="danger" title="Periksa lagi">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-senyum-alert>
    @endif

    @php
        $tiersCustomerOld = old('tiers_customer', isset($product->tiers) ? $product->tiers->where('channel', 'customer')->values()->map(fn ($t) => ['min_qty' => $t->min_qty, 'max_qty' => $t->max_qty, 'price' => $t->price])->toArray() : []);
        $tiersDistributorOld = old('tiers_distributor', isset($product->tiers) ? $product->tiers->where('channel', 'distributor')->values()->map(fn ($t) => ['min_qty' => $t->min_qty, 'max_qty' => $t->max_qty, 'price' => $t->price])->toArray() : []);
    @endphp

    <x-senyum-card>
        <form method="POST" enctype="multipart/form-data"
              action="{{ $mode === 'create' ? route('developer.produk.store') : route('developer.produk.update', $product) }}"
              class="grid sm:grid-cols-2 gap-4">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="sm:col-span-2">
                <label class="sn-label" for="f-name">Nama produk *</label>
                <input id="f-name" name="name" type="text" value="{{ old('name', $product->name) }}" class="sn-input @error('name') sn-input--error @enderror" maxlength="255" required>
                @error('name') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="sn-label" for="f-sku">SKU *</label>
                <input id="f-sku" name="sku" type="text" value="{{ old('sku', $product->sku) }}" class="sn-input @error('sku') sn-input--error @enderror" maxlength="100" required>
                @error('sku') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="sn-label" for="f-slug">Slug (opsional)</label>
                <input id="f-slug" name="slug" type="text" value="{{ old('slug', $product->slug) }}" class="sn-input @error('slug') sn-input--error @enderror" maxlength="255" placeholder="Otomatis dari nama bila kosong">
                @error('slug') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="sn-label" for="f-price">Harga Customer (Rp) *</label>
                <input id="f-price" name="price" type="number" min="0" step="1" value="{{ old('price', $product->price) }}" class="sn-input @error('price') sn-input--error @enderror" required>
                @error('price') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="sn-label" for="f-dist-price">Harga Distributor (Rp) *</label>
                <input id="f-dist-price" name="distributor_price" type="number" min="0" step="1" value="{{ old('distributor_price', $product->distributor_price) }}" class="sn-input @error('distributor_price') sn-input--error @enderror" required>
                @error('distributor_price') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="sn-label" for="f-status">Status *</label>
                <select id="f-status" name="status" class="sn-select @error('status') sn-input--error @enderror" required>
                    @foreach (['draft' => 'Draf', 'active' => 'Aktif', 'archived' => 'Diarsipkan'] as $v => $l)
                        <option value="{{ $v }}" @selected(old('status', $product->status) === $v)>{{ $l }}</option>
                    @endforeach
                </select>
                @error('status') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="sn-label" for="f-sort">Urutan tampil</label>
                <input id="f-sort" name="sort_order" type="number" min="0" step="1" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="sn-input @error('sort_order') sn-input--error @enderror">
                @error('sort_order') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-2">
                <input id="f-featured" name="featured" type="checkbox" value="1" @checked(old('featured', (bool) $product->featured)) class="w-5 h-5 accent-black">
                <label for="f-featured" class="text-sm font-bold">Jadikan Produk Unggulan</label>
            </div>

            <div class="sm:col-span-2">
                <label class="sn-label" for="f-image">Gambar sampul (JPG/PNG/WebP, maks 5MB)</label>
                <input id="f-image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" class="sn-input @error('image') sn-input--error @enderror">
                @if ($product->image)
                    <p class="sn-help">Sampul saat ini: <span class="font-mono">{{ $product->image }}</span> — unggah file baru untuk mengganti.</p>
                @endif
                @error('image') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="sn-label" for="f-images">Galeri tambahan (maks total 5 gambar)</label>
                <input id="f-images" name="images[]" type="file" accept=".jpg,.jpeg,.png,.webp" multiple class="sn-input @error('images') sn-input--error @enderror">
                <p class="sn-help">Boleh pilih beberapa file sekaligus. Kelola urutan/hapus lewat panel galeri di bawah (mode EDIT).</p>
                @error('images') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="sn-label" for="f-short">Deskripsi singkat</label>
                <input id="f-short" name="short_description" type="text" value="{{ old('short_description', $product->short_description) }}" class="sn-input @error('short_description') sn-input--error @enderror" maxlength="500">
                @error('short_description') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="sn-label" for="f-desc">Deskripsi</label>
                <textarea id="f-desc" name="description" rows="4" class="sn-textarea @error('description') sn-input--error @enderror">{{ old('description', $product->description) }}</textarea>
                @error('description') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="sn-label" for="f-ing">Komposisi (ingredients)</label>
                <textarea id="f-ing" name="ingredients" rows="3" class="sn-textarea @error('ingredients') sn-input--error @enderror">{{ old('ingredients', $product->ingredients) }}</textarea>
                @error('ingredients') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="sn-label" for="f-avail">Catatan ketersediaan</label>
                <input id="f-avail" name="availability_note" type="text" value="{{ old('availability_note', $product->availability_note) }}" class="sn-input @error('availability_note') sn-input--error @enderror" maxlength="255">
                @error('availability_note') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2 border-t-[3px] border-black pt-4">
                <p class="sn-label">Tingkat harga Customer (dinamis, opsional)</p>
                <p class="sn-help mb-2">Contoh: 1–11 @ harga normal, 12+ @ harga grosir. Maks 1 baris tanpa batas maksimum.</p>
                <div id="tiers-customer" class="space-y-2">
                    @foreach ($tiersCustomerOld as $i => $row)
                        <div class="grid grid-cols-4 gap-2 items-end" data-tier-row>
                            <div><label class="sn-label">Min</label><input type="number" min="1" name="tiers_customer[{{ $i }}][min_qty]" value="{{ $row['min_qty'] ?? '' }}" class="sn-input" placeholder="1"></div>
                            <div><label class="sn-label">Maks (kosong = ∞)</label><input type="number" min="1" name="tiers_customer[{{ $i }}][max_qty]" value="{{ $row['max_qty'] ?? '' }}" class="sn-input" placeholder="11"></div>
                            <div><label class="sn-label">Harga</label><input type="number" min="0" name="tiers_customer[{{ $i }}][price]" value="{{ $row['price'] ?? '' }}" class="sn-input" placeholder="25000"></div>
                            <div><button type="button" class="sn-btn sn-btn-danger sn-btn-sm" data-tier-remove>HAPUS</button></div>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="sn-btn sn-btn-ghost sn-btn-sm mt-2" data-tier-add="tiers_customer">+ Tambah baris customer</button>
            </div>

            <div class="sm:col-span-2 border-t-[3px] border-black pt-4">
                <p class="sn-label">Tingkat harga Distributor (dinamis, opsional)</p>
                <p class="sn-help mb-2">Berlaku untuk pembelian via Purchase Order distributor.</p>
                <div id="tiers-distributor" class="space-y-2">
                    @foreach ($tiersDistributorOld as $i => $row)
                        <div class="grid grid-cols-4 gap-2 items-end" data-tier-row>
                            <div><label class="sn-label">Min</label><input type="number" min="1" name="tiers_distributor[{{ $i }}][min_qty]" value="{{ $row['min_qty'] ?? '' }}" class="sn-input" placeholder="1"></div>
                            <div><label class="sn-label">Maks (kosong = ∞)</label><input type="number" min="1" name="tiers_distributor[{{ $i }}][max_qty]" value="{{ $row['max_qty'] ?? '' }}" class="sn-input" placeholder="11"></div>
                            <div><label class="sn-label">Harga</label><input type="number" min="0" name="tiers_distributor[{{ $i }}][price]" value="{{ $row['price'] ?? '' }}" class="sn-input" placeholder="20000"></div>
                            <div><button type="button" class="sn-btn sn-btn-danger sn-btn-sm" data-tier-remove>HAPUS</button></div>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="sn-btn sn-btn-ghost sn-btn-sm mt-2" data-tier-add="tiers_distributor">+ Tambah baris distributor</button>
                @error('tiers') <p class="sn-error">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2 flex flex-wrap gap-2">
                <button type="submit" class="sn-btn sn-btn-tosca sn-btn-sm">{{ $mode === 'create' ? 'SIMPAN produk' : 'SIMPAN perubahan' }}</button>
                <a href="{{ route('developer.produk') }}" class="sn-btn sn-btn-ghost sn-btn-sm">Batal</a>
            </div>
        </form>
    </x-senyum-card>

    @if ($mode === 'edit')
        <x-senyum-card title="Galeri ({{ $product->images->count() }}/5)" kicker="Gallery">
            @if ($product->images->isEmpty())
                <x-senyum-empty-state title="Belum ada galeri" message="Tambahkan gambar lewat form di bawah." />
            @else
                <ul class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ($product->images as $img)
                        <li class="border-2 border-black rounded-md p-2 bg-white">
                            <img src="{{ asset('storage/' . ltrim($img->path, '/')) }}" alt="Gambar {{ $product->name }}" class="w-full h-28 object-cover border-2 border-black rounded-sm" loading="lazy">
                            <p class="font-mono text-[10px] text-neutral-500 truncate mt-1">#{{ $img->sort_order }} · {{ $img->path }}</p>
                            <div class="flex flex-wrap gap-1 mt-2">
                                <form method="POST" action="{{ route('developer.produk.images.move', [$product, $img]) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="left">
                                    <button type="submit" class="sn-btn sn-btn-ghost sn-btn-sm" aria-label="Geser kiri">←</button>
                                </form>
                                <form method="POST" action="{{ route('developer.produk.images.move', [$product, $img]) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="right">
                                    <button type="submit" class="sn-btn sn-btn-ghost sn-btn-sm" aria-label="Geser kanan">→</button>
                                </form>
                                <form method="POST" action="{{ route('developer.produk.images.destroy', [$product, $img]) }}" onsubmit="return confirm('HAPUS gambar ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="sn-btn sn-btn-danger sn-btn-sm">HAPUS</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
            @if ($product->images->count() < 5)
                <form method="POST" enctype="multipart/form-data" action="{{ route('developer.produk.images.store', $product) }}" class="flex flex-wrap gap-2 items-end mt-4">
                    @csrf
                    <div class="grow min-w-52">
                        <label class="sn-label" for="g-images">Tambah gambar</label>
                        <input id="g-images" name="images[]" type="file" accept=".jpg,.jpeg,.png,.webp" multiple class="sn-input" required>
                    </div>
                    <button type="submit" class="sn-btn sn-btn-tosca sn-btn-sm">SIMPAN gambar</button>
                </form>
            @endif
        </x-senyum-card>

        <x-senyum-card title="Zona berbahaya" kicker="Danger zone">
            <div class="flex flex-wrap gap-2">
                @if (! $product->featured)
                    <button type="button" data-modal-open="#modal-featured" class="sn-btn sn-btn-tosca sn-btn-sm">✓ Jadikan Produk Unggulan</button>
                @endif
                @if ($product->status !== 'archived')
                    <button type="button" data-modal-open="#modal-archive" class="sn-btn sn-btn-yellow sn-btn-sm">NONAKTIFKAN (arsip)</button>
                @endif
                <button type="button" data-modal-open="#modal-delete" class="sn-btn sn-btn-danger sn-btn-sm">HAPUS</button>
            </div>
        </x-senyum-card>

        <x-senyum-modal id="modal-featured" title="Jadikan produk unggulan?" confirm="Ya, unggulkan">
            <p>Produk <strong>{{ $product->name }}</strong> akan menggantikan produk unggulan saat ini.</p>
            <form method="POST" action="{{ route('developer.produk.featured', $product) }}" class="mt-4">
                @csrf
            </form>
        </x-senyum-modal>

        <x-senyum-modal id="modal-archive" title="NONAKTIFKAN produk?" confirm="Ya, arsipkan">
            <p>Produk <strong>{{ $product->name }}</strong> akan diarsipkan (tidak dihapus) dan tidak bisa dijual.</p>
            <form method="POST" action="{{ route('developer.produk.archive', $product) }}" class="mt-4">
                @csrf
            </form>
        </x-senyum-modal>

        <x-senyum-modal id="modal-delete" title="HAPUS produk?" confirm="Ya, hapus">
            <p>Produk <strong>{{ $product->name }}</strong> akan dihapus (arsip otomatis, soft delete).</p>
            <form method="POST" action="{{ route('developer.produk.destroy', $product) }}" class="mt-4">
                @csrf
                @method('DELETE')
            </form>
        </x-senyum-modal>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    var counters = { tiers_customer: {{ count($tiersCustomerOld) }}, tiers_distributor: {{ count($tiersDistributorOld) }} };
    document.addEventListener('click', function (e) {
        var add = e.target.closest('[data-tier-add]');
        if (add) {
            var key = add.getAttribute('data-tier-add');
            var wrap = document.getElementById(key === 'tiers_customer' ? 'tiers-customer' : 'tiers-distributor');
            var i = counters[key]++;
            var row = document.createElement('div');
            row.className = 'grid grid-cols-4 gap-2 items-end';
            row.setAttribute('data-tier-row', '');
            row.innerHTML =
                '<div><label class="sn-label">Min</label><input type="number" min="1" name="' + key + '[' + i + '][min_qty]" class="sn-input" placeholder="1"></div>' +
                '<div><label class="sn-label">Maks (kosong = ∞)</label><input type="number" min="1" name="' + key + '[' + i + '][max_qty]" class="sn-input" placeholder="11"></div>' +
                '<div><label class="sn-label">Harga</label><input type="number" min="0" name="' + key + '[' + i + '][price]" class="sn-input" placeholder="25000"></div>' +
                '<div><button type="button" class="sn-btn sn-btn-danger sn-btn-sm" data-tier-remove>HAPUS</button></div>';
            wrap.appendChild(row);
            return;
        }
        var del = e.target.closest('[data-tier-remove]');
        if (del) {
            var r = del.closest('[data-tier-row]');
            if (r) r.remove();
        }
    });
})();
</script>
@endpush
