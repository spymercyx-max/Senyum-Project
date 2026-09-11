<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $products = Product::active()
            ->with(['inventory', 'tiers'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%');
            })
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        $products->getCollection()->each(function (Product $product): void {
            $product->setAttribute('stock', $product->inventory?->available ?? 0);
        });

        return view('public.products', [
            'products' => $products,
        ]);
    }

    public function show(string $slug, WhatsAppService $wa): View
    {
        $product = Product::active()
            ->with(['inventory', 'images', 'tiers'])
            ->where('slug', $slug)
            ->first();

        if (! $product) {
            abort(404);
        }

        $product->setAttribute('stock', $product->inventory?->available ?? 0);

        $message = "Halo Admin Kretek Senyum,\n\nSaya ingin menanyakan produk:\n\n"
            . $product->name
            . "\n\nMohon informasi mengenai ketersediaan dan proses pemesanannya.\n\nTerima kasih.";

        $waUrl = $wa->productInquiryUrl($product, $message);

        $related = Product::active()
            ->with(['inventory', 'tiers'])
            ->where('id', '!=', $product->id)
            ->orderBy('sort_order')
            ->take(3)
            ->get()
            ->each(function (Product $item): void {
                $item->setAttribute('stock', $item->inventory?->available ?? 0);
            });

        return view('public.product-detail', [
            'product' => $product,
            'waUrl' => $waUrl,
            'related' => $related,
        ]);
    }
}
