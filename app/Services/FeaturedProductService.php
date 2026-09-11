<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class FeaturedProductService
{
    public function setFeatured(Product $product): Product
    {
        if ($product->status !== 'active') {
            throw new \InvalidArgumentException('Hanya produk aktif yang bisa menjadi Produk Unggulan.');
        }

        return DB::transaction(function () use ($product) {
            Product::where('featured', true)
                ->where('id', '!=', $product->id)
                ->update(['featured' => false]);

            $product->update(['featured' => true]);

            return $product->refresh();
        });
    }

    public function clearFeatured(): void
    {
        Product::where('featured', true)->update(['featured' => false]);
    }

    public function featured(): ?Product
    {
        return Product::where('featured', true)->first();
    }
}
