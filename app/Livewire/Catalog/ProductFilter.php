<?php

namespace App\Livewire\Catalog;

use App\Models\Product;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductFilter extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    public function mount(string $q = ''): void
    {
        $this->q = $q;
    }

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $keyword = trim($this->q);

        $products = Product::active()
            ->with(['inventory', 'tiers'])
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->orderBy('sort_order')
            ->paginate(12);

        $products->getCollection()->each(function (Product $product): void {
            $product->setAttribute('stock', $product->inventory?->available ?? 0);
        });

        return view('livewire.catalog.product-filter', [
            'products' => $products,
        ]);
    }
}
