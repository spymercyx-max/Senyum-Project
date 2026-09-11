<?php

namespace App\Http\Controllers\Api\Developer;

use App\Http\Controllers\Controller;
use App\Http\Resources\DistributorMapResource;
use App\Http\Resources\OutletMapResource;
use App\Http\Resources\TerritoryMapResource;
use App\Models\Outlet;
use App\Models\Territory;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;

/**
 * Internal Map API — JSON konsisten {data, meta}.
 * Semua angka dari query nyata. Filter memengaruhi dataset,
 * bukan sekadar menyembunyikan elemen HTML.
 *
 * Coverage per territory (terdokumentasi, deterministik):
 * - covered   : >=1 outlet AKTIF + ada transaksi/kunjungan <=30 hari
 * - partial   : ada outlet tapi (tidak ada yang aktif ATAU tanpa aktivitas 30 hari)
 * - uncovered : ada distributor tapi nol outlet, atau tanpa distributor
 */
class MapController extends Controller
{
    public function statistics(Request $request)
    {
        $t = $this->filteredTerritories($request);

        $territoryIds = (clone $t)->pluck('territories.id');
        $outlets = Outlet::whereIn('territory_id', $territoryIds);
        $activeOutlets = (clone $outlets)->where('status', 'active')->count();
        $validLocated = (clone $outlets)->whereNotNull('latitude')->whereNotNull('longitude')
            ->where(fn ($w) => $w->where('latitude', '!=', 0)->orWhere('longitude', '!=', 0))->count();

        $distributors = User::where('role', 'distributor')->whereHas(
            'distributorProfile', fn ($q) => $q->whereIn('territory_id', $territoryIds)
        )->count();

        $coverage = $this->coverageFor($territoryIds);

        return response()->json([
            'data' => [
                'total_territories' => (clone $t)->count(),
                'total_distributors' => $distributors,
                'total_outlets' => $totalOutlets = (clone $outlets)->count(),
                'active_outlets' => $activeOutlets,
                'outlets_with_valid_location' => $validLocated,
                'outlets_without_valid_location' => $totalOutlets - $validLocated,
                'coverage_rate' => $coverage['rate'],
                'active_areas' => $coverage['covered'],
            ],
            'meta' => ['generated_at' => now()->toIso8601String()],
        ]);
    }

    public function territories(Request $request)
    {
        $rows = $this->filteredTerritories($request)
            ->withCount([
                'distributorProfiles as distributors_count',
                'outlets as outlets_count',
                'outlets as active_outlets_count' => fn ($q) => $q->where('status', 'active'),
            ])
            ->withAvg('outlets as center_lat', 'latitude')
            ->withAvg('outlets as center_lng', 'longitude')
            ->paginate(50);

        $ids = $rows->getCollection()->pluck('id');
        $cov = $this->coverageMap($ids);
        $rows->getCollection()->each(fn ($row) => $row->coverage = $cov[$row->id] ?? 'uncovered');

        return TerritoryMapResource::collection($rows)->additional([
            'meta' => ['generated_at' => now()->toIso8601String()],
        ]);
    }

    public function distributors(Request $request)
    {
        $q = User::where('role', 'distributor')->with('distributorProfile.territory');

        if ($request->filled('distributor_status')) {
            $q->whereHas('distributorProfile', fn ($w) => $w->where('status', $request->string('distributor_status')));
        }
        if ($request->filled('territory_id')) {
            $q->whereHas('distributorProfile', fn ($w) => $w->where('territory_id', $request->integer('territory_id')));
        }
        if ($request->filled('city')) {
            $q->whereHas('distributorProfile.territory', fn ($w) => $w->where('city', 'like', '%'.$request->string('city').'%'));
        }
        if ($request->filled('q')) {
            $s = '%'.$request->string('q').'%';
            $q->where(fn ($w) => $w->where('name', 'like', $s)->orWhere('username', 'like', $s));
        }

        $rows = $q->withCount('outlets')
            ->withAvg('outlets as center_lat', 'latitude')
            ->withAvg('outlets as center_lng', 'longitude')
            ->paginate(50);

        return DistributorMapResource::collection($rows);
    }

    public function outlets(Request $request)
    {
        $q = Outlet::with('distributor:id,name')->whereNotNull('latitude')->whereNotNull('longitude');

        foreach (['territory_id', 'distributor_id', 'city', 'district'] as $f) {
            if ($request->filled($f)) {
                $q->where($f === 'city' || $f === 'district'
                    ? fn ($w) => $w
                    : $f, $f === 'city' || $f === 'district' ? 'like' : '=', $f === 'city' || $f === 'district'
                    ? '%'.$request->string($f).'%'
                    : $request->input($f));
            }
        }
        if ($request->filled('outlet_status')) {
            $q->where('status', $request->string('outlet_status'));
        }

        // Bounding box — hanya data dalam viewport yang dikirim.
        foreach ([['north', '<='], ['south', '>='], ['east', '<='], ['west', '>=']] as [$key, $op]) {
            if ($request->filled($key) && is_numeric($request->input($key))) {
                $col = in_array($key, ['north', 'south']) ? 'latitude' : 'longitude';
                $q->where($col, $op, (float) $request->input($key));
            }
        }

        if ($request->filled('min_volume')) {
            $q->withSum(['transactions as volume' => function ($w) use ($request) {
                $this->applyDateRange($w, $request, 'sold_at');
            }], 'total_amount')->having('volume', '>=', (int) $request->input('min_volume'));
        }

        $rows = $q->withMax('visits as last_visit_at', 'visited_at')
            ->withMax('transactions as last_transaction_at', 'sold_at')
            ->limit(500)
            ->get(['id', 'name', 'address', 'city', 'district', 'phone', 'status', 'latitude', 'longitude', 'distributor_id', 'territory_id']);

        // Satu query untuk foto sampul (tanpa N+1, tanpa seluruh relasi).
        $covers = \App\Models\OutletImage::whereIn('outlet_id', $rows->pluck('id'))
            ->orderBy('outlet_id')->orderBy('sort_order')->orderBy('id')
            ->get(['outlet_id', 'path', 'disk'])
            ->unique('outlet_id')->keyBy('outlet_id');
        $rows->each(function ($o) use ($covers) {
            $img = $covers->get($o->id);
            $o->cover_url = $img ? \Illuminate\Support\Facades\Storage::disk($img->disk)->url($img->path) : null;
        });

        return OutletMapResource::collection($rows)->additional([
            'meta' => ['count' => $rows->count(), 'truncated_at' => 500],
        ]);
    }

    public function activity(Request $request)
    {
        $visits = Visit::with('outlet:id,name,latitude,longitude')
            ->whereHas('outlet', fn ($q) => $q->whereNotNull('latitude'));
        $transactions = Transaction::with('outlet:id,name,latitude,longitude')
            ->whereHas('outlet', fn ($q) => $q->whereNotNull('latitude'));

        $this->applyDateRange($visits, $request, 'visited_at');
        $this->applyDateRange($transactions, $request, 'sold_at');

        if ($request->filled('territory_id')) {
            $visits->whereHas('outlet', fn ($q) => $q->where('territory_id', $request->integer('territory_id')));
            $transactions->whereHas('outlet', fn ($q) => $q->where('territory_id', $request->integer('territory_id')));
        }

        return response()->json([
            'data' => [
                'visits' => $visits->limit(200)->get()->map(fn ($v) => [
                    'id' => $v->id, 'outlet' => $v->outlet?->name,
                    'lat' => (float) $v->outlet?->latitude, 'lng' => (float) $v->outlet?->longitude,
                    'at' => $v->visited_at,
                ]),
                'transactions' => $transactions->limit(200)->get()->map(fn ($t) => [
                    'id' => $t->id, 'outlet' => $t->outlet?->name,
                    'lat' => (float) $t->outlet?->latitude, 'lng' => (float) $t->outlet?->longitude,
                    'total' => (int) $t->total_amount, 'at' => $t->sold_at,
                ]),
            ],
        ]);
    }

    public function coverage(Request $request)
    {
        $ids = $this->filteredTerritories($request)->pluck('territories.id');

        return response()->json(['data' => $this->coverageFor($ids)]);
    }

    public function search(Request $request)
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);
        $s = '%'.$request->string('q').'%';

        $outlets = Outlet::where(fn ($q) => $q->where('name', 'like', $s)->orWhere('address', 'like', $s))
            ->limit(10)->get(['id', 'name', 'city', 'district', 'latitude', 'longitude', 'status']);
        $distributors = User::where('role', 'distributor')
            ->where(fn ($q) => $q->where('name', 'like', $s)->orWhere('username', 'like', $s))
            ->with('distributorProfile.territory')->limit(10)->get();
        $territories = Territory::where(fn ($q) => $q->where('city', 'like', $s)->orWhere('district', 'like', $s))
            ->limit(10)->get();

        return response()->json([
            'data' => [
                'outlets' => $outlets->map(fn ($o) => [
                    'id' => $o->id, 'name' => $o->name, 'city' => $o->city, 'district' => $o->district,
                    'lat' => $o->latitude !== null ? (float) $o->latitude : null,
                    'lng' => $o->longitude !== null ? (float) $o->longitude : null,
                ]),
                'distributors' => DistributorMapResource::collection($distributors),
                'territories' => TerritoryMapResource::collection($territories),
            ],
        ]);
    }

    // ---------- helpers ----------

    protected function filteredTerritories(Request $request)
    {
        $q = Territory::query();
        if ($request->filled('city')) {
            $q->where('city', 'like', '%'.$request->string('city').'%');
        }
        if ($request->filled('district')) {
            $q->where('district', 'like', '%'.$request->string('district').'%');
        }
        if ($request->filled('territory_id')) {
            $q->where('id', $request->integer('territory_id'));
        }
        return $q;
    }

    protected function applyDateRange($query, Request $request, string $column): void
    {
        if ($request->filled('date_from')) {
            $query->whereDate($column, '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate($column, '<=', $request->date('date_to'));
        }
    }

    /** @param \Illuminate\Support\Collection<int> $ids */
    protected function coverageMap($ids): array
    {
        $since = now()->subDays(30);
        $map = [];

        foreach ($ids as $id) {
            $outletCount = Outlet::where('territory_id', $id)->count();
            $activeCount = Outlet::where('territory_id', $id)->where('status', 'active')->count();
            $hasDistributor = \App\Models\DistributorProfile::where('territory_id', $id)->exists();

            if (! $hasDistributor || $outletCount === 0) {
                $map[$id] = 'uncovered';
                continue;
            }

            $recentTx = Transaction::whereHas('outlet', fn ($q) => $q->where('territory_id', $id))
                ->where('sold_at', '>=', $since)->exists();
            $recentVisit = Visit::whereHas('outlet', fn ($q) => $q->where('territory_id', $id))
                ->where('visited_at', '>=', $since)->exists();

            $map[$id] = ($activeCount > 0 && ($recentTx || $recentVisit)) ? 'covered' : 'partial';
        }

        return $map;
    }

    protected function coverageFor($ids): array
    {
        $map = $this->coverageMap($ids);
        $total = count($map);
        $covered = count(array_filter($map, fn ($v) => $v === 'covered'));

        return [
            'covered' => $covered,
            'partial' => count(array_filter($map, fn ($v) => $v === 'partial')),
            'uncovered' => count(array_filter($map, fn ($v) => $v === 'uncovered')),
            'rate' => $total > 0 ? round($covered / $total * 100, 1) : 0,
        ];
    }
}
