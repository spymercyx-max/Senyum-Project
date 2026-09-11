<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\OutletImage;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OutletImageController extends Controller
{
    public function store(Request $request, Outlet $outlet): RedirectResponse
    {
        abort_if((int) $outlet->distributor_id !== (int) $request->user()->id, 403, 'Outlet ini bukan milik Anda.');

        $data = $request->validate([
            'images' => ['required', 'array', 'max:5'],
            'images.*' => ImageUpload::rules(false),
        ], [], [
            'images' => 'foto outlet',
            'images.*' => 'foto outlet',
        ]);

        $files = $data['images'] ?? [];
        $existingCount = $outlet->images()->count();

        if ($existingCount + count($files) > 5) {
            return back()->withErrors(['images' => "Maksimal 5 foto per outlet (saat ini {$existingCount} tersimpan)."])->withInput();
        }

        $nextOrder = (int) ($outlet->images()->max('sort_order') ?? -1) + 1;

        foreach (array_values($files) as $i => $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $path = ImageUpload::store($file, 'outlets');

            $outlet->images()->create([
                'path' => $path,
                'disk' => 'public',
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'sort_order' => $nextOrder + $i,
            ]);
        }

        return back()->with('success', 'Foto outlet berhasil ditambahkan.');
    }

    public function destroy(Request $request, Outlet $outlet, OutletImage $image): RedirectResponse
    {
        abort_if((int) $outlet->distributor_id !== (int) $request->user()->id, 403, 'Outlet ini bukan milik Anda.');
        abort_if((int) $image->outlet_id !== (int) $outlet->id, 404);

        ImageUpload::delete($image->path, $image->disk ?? 'public');
        $image->delete();

        return back()->with('success', 'Foto outlet berhasil dihapus.');
    }
}
