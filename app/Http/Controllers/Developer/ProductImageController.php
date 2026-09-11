<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'images' => ['required', 'array', 'max:5'],
            'images.*' => ImageUpload::rules(false),
        ]);

        $existing = $product->images()->count();
        $slots = 5 - $existing;

        if ($slots <= 0) {
            return back()->with('error', 'Galeri sudah penuh. Maksimal 5 gambar per produk.');
        }

        $files = array_values(array_filter((array) $request->file('images', [])));
        if (count($files) > $slots) {
            return back()->with('error', "Sisa slot galeri tinggal {$slots} gambar. Maksimal 5 gambar per produk.");
        }

        $order = (int) ($product->images()->max('sort_order') ?? -1) + 1;

        foreach ($files as $file) {
            $path = ImageUpload::store($file, 'products');
            $product->images()->create(['path' => $path, 'disk' => 'public', 'sort_order' => $order++]);
        }

        ActivityLog::create([
            'actor_id' => auth()->id(),
            'action' => 'product.image_added',
            'entity' => Product::class,
            'entity_id' => $product->id,
            'metadata' => ['added' => count($files)],
            'ip' => $request->ip(),
        ]);

        return back()->with('success', count($files) . ' gambar berhasil ditambahkan ke ' . $product->name . '.');
    }

    public function destroy(Product $product, ProductImage $image): RedirectResponse
    {
        abort_if($image->product_id !== $product->id, 404);

        ImageUpload::delete($image->path, $image->disk ?? 'public');

        $coverRelative = $product->image ? preg_replace('#^storage/#', '', ltrim($product->image, '/')) : null;
        if ($coverRelative && $coverRelative === $image->path) {
            $product->update(['image' => null]);
        }

        $image->delete();

        ActivityLog::create([
            'actor_id' => auth()->id(),
            'action' => 'product.image_removed',
            'entity' => Product::class,
            'entity_id' => $product->id,
            'metadata' => ['path' => $image->path],
            'ip' => request()->ip(),
        ]);

        return back()->with('success', 'Gambar berhasil dihapus.');
    }

    public function move(Request $request, Product $product, ProductImage $image): RedirectResponse
    {
        abort_if($image->product_id !== $product->id, 404);

        $data = $request->validate([
            'direction' => ['required', 'in:left,right'],
        ]);

        $neighbor = $data['direction'] === 'left'
            ? $product->images()->where('sort_order', '<', $image->sort_order)->orderByDesc('sort_order')->first()
            : $product->images()->where('sort_order', '>', $image->sort_order)->orderBy('sort_order')->first();

        if (! $neighbor) {
            return back()->with('error', 'Gambar sudah di posisi paling ujung.');
        }

        [$image->sort_order, $neighbor->sort_order] = [$neighbor->sort_order, $image->sort_order];
        $image->save();
        $neighbor->save();

        return back()->with('success', 'Urutan gambar berhasil diubah.');
    }
}
