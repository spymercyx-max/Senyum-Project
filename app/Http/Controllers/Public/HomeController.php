<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WhatsAppService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(WhatsAppService $wa): View
    {
        $featured = Product::active()
            ->featured()
            ->with(['inventory', 'tiers'])
            ->orderBy('sort_order')
            ->first();

        if ($featured) {
            $featured->setAttribute('stock', $featured->inventory?->available ?? 0);
        }

        $products = Product::active()
            ->with(['inventory', 'tiers'])
            ->orderBy('sort_order')
            ->take(3)
            ->get()
            ->each(function (Product $product): void {
                $product->setAttribute('stock', $product->inventory?->available ?? 0);
            });

        return view('public.home', [
            'featured' => $featured,
            'products' => $products,
            'waOrder' => $wa->orderNumber(),
            'waDev' => $wa->developerNumber(),
        ]);
    }

    public function about(): View
    {
        return view('public.about');
    }
}
