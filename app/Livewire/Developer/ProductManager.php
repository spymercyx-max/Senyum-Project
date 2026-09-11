<?php

namespace App\Livewire\Developer;

use App\Models\Product;
use App\Services\FeaturedProductService;
use Livewire\Component;
use Livewire\WithPagination;

class ProductManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public ?string $notice = null;

    public ?string $error = null;

    public function mount(string $search = '', ?string $status = ''): void
    {
        $this->search = $search;
        $this->status = in_array($status, ['draft', 'active', 'archived'], true) ? $status : '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function setFeatured(int $productId, FeaturedProductService $service): void
    {
        $product = Product::find($productId);

        if (! $product) {
            $this->error = 'Produk tidak ditemukan.';

            return;
        }

        if ($product->status === 'archived') {
            $this->error = 'Produk yang diarsipkan tidak bisa dijadikan produk unggulan.';

            return;
        }

        try {
            $service->setFeatured($product);
        } catch (\Throwable) {
            $this->error = 'Gagal menjadikan produk unggulan. Silakan coba lagi.';

            return;
        }

        $this->error = null;
        $this->notice = "Produk {$product->name} kini menjadi produk unggulan.";
    }

    public function render()
    {
        $products = Product::with(['inventory', 'tiers'])
            ->withCount('tiers')
            ->when($this->search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%")))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.developer.product-manager', compact('products'));
    }
}
