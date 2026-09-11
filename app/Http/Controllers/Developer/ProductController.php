<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Services\FeaturedProductService;
use App\Services\InventoryService;
use App\Services\ProductPricingService;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        if (! in_array($status, ['draft', 'active', 'archived'], true)) {
            $status = null;
        }

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('status', 'active')->count(),
            'draft' => Product::where('status', 'draft')->count(),
            'archived' => Product::where('status', 'archived')->count(),
            'featured' => Product::where('featured', true)->first(),
        ];

        // Tabel utama dirender Livewire ProductManager (search/filter/featured);
        // ?q & ?status diteruskan sebagai state awal agar tautan filter bekerja.
        return view('developer.produk', compact('stats', 'q', 'status'));
    }

    public function create(): View
    {
        $product = new Product([
            'status' => 'draft',
            'featured' => false,
            'sort_order' => 0,
            'price' => 0,
            'distributor_price' => 0,
        ]);
        $product->setRelation('tiers', collect());
        $product->setRelation('images', collect());

        return view('developer.produk-form', [
            'product' => $product,
            'mode' => 'create',
        ]);
    }

    public function store(
        ProductRequest $request,
        FeaturedProductService $featuredService,
        ProductPricingService $pricing,
        InventoryService $inventoryService,
    ): RedirectResponse {
        $validated = $request->validated();
        $validated['slug'] = $this->resolveSlug($validated['name'], $validated['slug'] ?? null);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        if ($request->hasFile('image')) {
            $validated['image'] = 'storage/' . ImageUpload::store($request->file('image'), 'products');
        } else {
            unset($validated['image']);
        }

        $wantsFeatured = $request->boolean('featured');
        $validated['featured'] = false;

        $tiersCustomer = $this->cleanTierRows($validated['tiers_customer'] ?? []);
        $tiersDistributor = $this->cleanTierRows($validated['tiers_distributor'] ?? []);
        unset($validated['tiers_customer'], $validated['tiers_distributor'], $validated['images']);

        try {
            if ($tiersCustomer !== []) {
                ProductPricingService::validateTiers($tiersCustomer);
            }
            if ($tiersDistributor !== []) {
                ProductPricingService::validateTiers($tiersDistributor);
            }
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['tiers' => $e->getMessage()])->withInput();
        }

        $product = Product::create($validated);

        if ($tiersCustomer !== []) {
            $pricing->syncChannel($product, 'customer', $tiersCustomer);
        }
        if ($tiersDistributor !== []) {
            $pricing->syncChannel($product, 'distributor', $tiersDistributor);
        }

        $this->storeGallery($request, $product);

        $inventoryService->forProduct($product);

        if ($wantsFeatured) {
            $featuredService->setFeatured($product);
        }

        $this->log('product.created', $product, ['name' => $product->name]);

        return redirect()->route('developer.produk')
            ->with('success', "Produk {$product->name} berhasil dibuat.");
    }

    public function edit(Product $product): View
    {
        $product->load(['inventory', 'tiers', 'images']);

        return view('developer.produk-form', [
            'product' => $product,
            'mode' => 'edit',
        ]);
    }

    public function update(
        ProductRequest $request,
        Product $product,
        FeaturedProductService $featuredService,
        ProductPricingService $pricing,
    ): RedirectResponse {
        $validated = $request->validated();
        $validated['slug'] = $this->resolveSlug($validated['name'], $validated['slug'] ?? null, $product->id);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? $product->sort_order ?? 0);

        if ($request->hasFile('image')) {
            $this->deleteCoverFile($product->image);
            $validated['image'] = 'storage/' . ImageUpload::store($request->file('image'), 'products');
        } else {
            unset($validated['image']);
        }

        $wantsFeatured = $request->boolean('featured');
        unset($validated['featured']);

        $tiersCustomer = $this->cleanTierRows($validated['tiers_customer'] ?? []);
        $tiersDistributor = $this->cleanTierRows($validated['tiers_distributor'] ?? []);
        unset($validated['tiers_customer'], $validated['tiers_distributor'], $validated['images']);

        try {
            if ($tiersCustomer !== []) {
                ProductPricingService::validateTiers($tiersCustomer);
            }
            if ($tiersDistributor !== []) {
                ProductPricingService::validateTiers($tiersDistributor);
            }
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['tiers' => $e->getMessage()])->withInput();
        }

        $product->update($validated);

        $pricing->syncChannel($product, 'customer', $tiersCustomer);
        $pricing->syncChannel($product, 'distributor', $tiersDistributor);

        $this->storeGallery($request, $product);

        if ($wantsFeatured && ! $product->featured) {
            $featuredService->setFeatured($product);
        } elseif (! $wantsFeatured && $product->fresh()->featured) {
            $product->update(['featured' => false]);
        }

        $this->log('product.updated', $product, ['name' => $product->name]);

        return redirect()->route('developer.produk')
            ->with('success', "Produk {$product->name} berhasil diperbarui.");
    }

    public function featured(Product $product, FeaturedProductService $featuredService): RedirectResponse
    {
        if ($product->status === 'archived') {
            return back()->with('error', 'Produk yang diarsipkan tidak bisa dijadikan produk unggulan.');
        }

        try {
            $featuredService->setFeatured($product);
        } catch (\Throwable) {
            return back()->with('error', 'Gagal menjadikan produk unggulan. Silakan coba lagi.');
        }

        $this->log('product.featured', $product, ['name' => $product->name]);

        return back()->with('success', "Produk {$product->name} kini menjadi produk unggulan.");
    }

    public function archive(Product $product): RedirectResponse
    {
        $product->update(['status' => 'archived', 'featured' => false]);

        $this->log('product.archived', $product, ['name' => $product->name]);

        return back()->with('success', "Produk {$product->name} berhasil diarsipkan.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->featured) {
            return back()->with('error', "Produk {$product->name} sedang menjadi produk unggulan sehingga tidak boleh dihapus. NONAKTIFKAN dulu status unggulannya.");
        }

        $name = $product->name;
        $product->delete();

        $this->log('product.deleted', $product, ['name' => $name]);

        return redirect()->route('developer.produk')
            ->with('success', "Produk {$name} berhasil dihapus (arsip otomatis).");
    }

    /**
     * Buang baris tier kosong dari form dinamis.
     *
     * @return array<int, array{min_qty:mixed,max_qty:mixed,price:mixed}>
     */
    private function cleanTierRows(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $clean = [];
        foreach (array_values($rows) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $min = $row['min_qty'] ?? null;
            $max = $row['max_qty'] ?? null;
            $price = $row['price'] ?? null;
            if (($min === null || $min === '') && ($max === null || $max === '') && ($price === null || $price === '')) {
                continue;
            }
            $clean[] = ['min_qty' => $min, 'max_qty' => $max, 'price' => $price];
        }

        return $clean;
    }

    /**
     * Simpan galeri images[] — total galeri dibatasi 5 per produk.
     */
    private function storeGallery(ProductRequest $request, Product $product): void
    {
        $files = $request->file('images', []);
        if (! is_array($files) || $files === []) {
            return;
        }

        $existing = $product->images()->count();
        $order = (int) ($product->images()->max('sort_order') ?? -1) + 1;

        foreach ($files as $file) {
            if ($file === null) {
                continue;
            }
            if ($existing >= 5) {
                break;
            }
            $path = ImageUpload::store($file, 'products');
            $product->images()->create(['path' => $path, 'disk' => 'public', 'sort_order' => $order++]);
            $existing++;
        }
    }

    private function resolveSlug(string $name, ?string $slug, ?int $ignoreId = null): string
    {
        $base = $slug ? Str::slug($slug) : Str::slug($name);
        $base = $base !== '' ? $base : 'produk';
        $candidate = $base;
        $i = 1;

        while (Product::where('slug', $candidate)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $i++;
            $candidate = $base . '-' . $i;
        }

        return $candidate;
    }

    /**
     * Hapus file sampul lama (kolom image menyimpan path URL "storage/...").
     */
    private function deleteCoverFile(?string $cover): void
    {
        if ($cover === null || $cover === '') {
            return;
        }

        $relative = preg_replace('#^storage/#', '', ltrim($cover, '/'));
        ImageUpload::delete($relative);
    }

    private function log(string $action, Product $product, array $metadata = []): void
    {
        ActivityLog::create([
            'actor_id' => auth()->id(),
            'action' => $action,
            'entity' => Product::class,
            'entity_id' => $product->id,
            'metadata' => $metadata,
            'ip' => request()->ip(),
        ]);
    }
}
