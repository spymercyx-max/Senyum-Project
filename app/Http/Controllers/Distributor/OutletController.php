<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\OutletRequest;
use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Services\MapService;
use App\Services\UnresolvableMapsLinkException;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OutletController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->loadMissing('distributorProfile.territory');
        $q = trim((string) $request->query('q', ''));

        $outlets = $user->outlets()->with(['images', 'territory'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('address', 'like', "%{$q}%")
                        ->orWhere('city', 'like', "%{$q}%")
                        ->orWhere('district', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('distributor.outlet', [
            'outlets' => $outlets,
            'q' => $q,
            'territory' => $user->distributorProfile?->territory?->displayName(),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user()->loadMissing('distributorProfile.territory');
        $territory = $user->distributorProfile?->territory;

        return view('distributor.outlet-create', [
            'territoryModel' => $territory,
            'territory' => $territory?->displayName(),
        ]);
    }

    public function store(OutletRequest $request, MapService $maps): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $territoryId = $user->distributorProfile?->territory_id;
        if (! $territoryId) {
            return back()->withErrors(['name' => 'Wilayah kerjamu belum dipasang. Hubungi developer sebelum menambah outlet.'])->withInput();
        }

        $rawLink = trim((string) ($data['google_maps_url'] ?? ''));

        try {
            $coords = $maps->coordinatesFromLink($rawLink);
        } catch (UnresolvableMapsLinkException $e) {
            return back()->withErrors(['google_maps_url' => $e->getMessage()])->withInput();
        }

        $images = $data['images'] ?? [];
        unset($data['images']);

        $existingCount = 0;
        if (count($images) + $existingCount > 5) {
            return back()->withErrors(['images' => 'Maksimal 5 foto per outlet.'])->withInput();
        }

        // Atomik: outlet + koordinat + gambar + log, atau rollback total.
        // Tidak pernah ada outlet dengan data lokasi setengah jadi.
        $outlet = \Illuminate\Support\Facades\DB::transaction(function () use (
            $territoryId, $user, $data, $rawLink, $coords, $images, $request
        ) {
            $outlet = Outlet::create([
                'territory_id' => $territoryId,
                'distributor_id' => $user->id,
                'name' => $data['name'],
                'address' => $data['address'],
                'city' => $data['city'],
                'district' => $data['district'],
                'phone' => $data['phone'] ?? null,
                'google_maps_url' => $rawLink,
                'latitude' => $coords['lat'],
                'longitude' => $coords['lng'],
                'location_source' => 'google_maps',
                'location_verified_at' => now(),
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
            ]);

            $this->storeImages($outlet, $images);

            ActivityLog::create([
                'actor_id' => $user->id,
                'action' => 'outlet.created',
                'entity' => Outlet::class,
                'entity_id' => $outlet->id,
                'metadata' => ['name' => $outlet->name],
                'ip' => $request->ip(),
            ]);

            return $outlet;
        });

        return redirect()->route('distributor.outlet.show', $outlet)
            ->with('success', "Outlet {$outlet->name} berhasil ditambahkan. Langkah berikutnya: catat kunjungan atau penjualan pertama.");
    }

    public function show(Request $request, Outlet $outlet): View|RedirectResponse
    {
        abort_if((int) $outlet->distributor_id !== (int) $request->user()->id, 403, 'Outlet ini bukan milik Anda.');

        $outlet->loadMissing(['images', 'territory', 'distributor']);

        $transactions = $outlet->transactions()->with('items')
            ->where('distributor_id', $request->user()->id)
            ->latest('sold_at')->limit(5)->get();

        $visits = $outlet->visits()
            ->where('distributor_id', $request->user()->id)
            ->latest('visited_at')->limit(5)->get();

        return view('distributor.outlet-detail', [
            'outlet' => $outlet,
            'images' => $outlet->images,
            'transactions' => $transactions,
            'visits' => $visits,
            'territory' => $request->user()->distributorProfile?->territory?->displayName(),
        ]);
    }

    public function edit(Request $request, Outlet $outlet): View
    {
        abort_if((int) $outlet->distributor_id !== (int) $request->user()->id, 403, 'Outlet ini bukan milik Anda.');

        $outlet->loadMissing(['images', 'territory']);
        $user = $request->user()->loadMissing('distributorProfile.territory');

        return view('distributor.outlet-edit', [
            'outlet' => $outlet,
            'images' => $outlet->images,
            'territoryModel' => $user->distributorProfile?->territory,
            'territory' => $user->distributorProfile?->territory?->displayName(),
        ]);
    }

    public function update(OutletRequest $request, Outlet $outlet, MapService $maps): RedirectResponse
    {
        abort_if((int) $outlet->distributor_id !== (int) $request->user()->id, 403, 'Outlet ini bukan milik Anda.');

        $user = $request->user();
        $data = $request->validated();

        $images = $data['images'] ?? [];
        unset($data['images']);

        $existingCount = $outlet->images()->count();
        if ($existingCount + count($images) > 5) {
            return back()->withErrors(['images' => "Maksimal 5 foto per outlet (saat ini {$existingCount} tersimpan)."])->withInput();
        }

        $newLink = trim((string) ($data['google_maps_url'] ?? ''));
        $oldLink = trim((string) ($outlet->google_maps_url ?? ''));

        $locationChanged = ($newLink !== $oldLink);

        if ($locationChanged) {
            try {
                $coords = $maps->coordinatesFromLink($newLink);
            } catch (UnresolvableMapsLinkException $e) {
                return back()->withErrors(['google_maps_url' => $e->getMessage()])->withInput();
            }

            $oldCoords = ['lat' => $outlet->latitude, 'lng' => $outlet->longitude];

            $outlet->fill([
                'name' => $data['name'],
                'address' => $data['address'],
                'city' => $data['city'],
                'district' => $data['district'],
                'phone' => $data['phone'] ?? null,
                'google_maps_url' => $newLink,
                'latitude' => $coords['lat'],
                'longitude' => $coords['lng'],
                'location_source' => 'google_maps',
                'location_verified_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);
            $outlet->save();

            ActivityLog::create([
                'actor_id' => $user->id,
                'action' => 'outlet.location_changed',
                'entity' => Outlet::class,
                'entity_id' => $outlet->id,
                'metadata' => [
                    'name' => $outlet->name,
                    'old' => ['url' => $oldLink, 'coords' => $oldCoords],
                    'new' => ['url' => $newLink, 'coords' => $coords],
                ],
                'ip' => $request->ip(),
            ]);
        } else {
            $outlet->fill([
                'name' => $data['name'],
                'address' => $data['address'],
                'city' => $data['city'],
                'district' => $data['district'],
                'phone' => $data['phone'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            $outlet->save();
        }

        $this->storeImages($outlet, $images);

        return redirect()->route('distributor.outlet.show', $outlet)
            ->with('success', "Outlet {$outlet->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, Outlet $outlet): RedirectResponse
    {
        abort_if((int) $outlet->distributor_id !== (int) $request->user()->id, 403, 'Outlet ini bukan milik Anda.');

        $name = $outlet->name;
        $outlet->delete();

        ActivityLog::create([
            'actor_id' => $request->user()->id,
            'action' => 'outlet.deleted',
            'entity' => Outlet::class,
            'entity_id' => $outlet->id,
            'metadata' => ['name' => $name],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('distributor.outlet')
            ->with('success', "Outlet {$name} berhasil dihapus. Langkah berikutnya: kelola outlet lainnya.");
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>  $files
     */
    protected function storeImages(Outlet $outlet, array $files): void
    {
        if (empty($files)) {
            return;
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
    }
}
