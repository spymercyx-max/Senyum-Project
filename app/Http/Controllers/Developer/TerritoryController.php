<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\Territory;
use App\Models\Transaction;
use App\Models\Visit;
use Illuminate\View\View;

class TerritoryController extends Controller
{
    public function index(): View
    {
        $metrics = [
            'total' => Territory::count(),
            'withDistributor' => Territory::whereHas('distributorProfiles')->count(),
            'activeOutlet' => Outlet::where('status', 'active')->count(),
            'activity' => Visit::count() + Transaction::count(),
        ];

        $territories = Territory::with(['distributorProfiles.user'])
            ->withCount(['distributorProfiles', 'outlets'])
            ->orderBy('city')
            ->orderBy('district')
            ->paginate(15);

        $txStats = Transaction::selectRaw('outlets.territory_id, COUNT(*) as tx_count, COALESCE(SUM(transactions.total_amount), 0) as tx_total')
            ->join('outlets', 'outlets.id', '=', 'transactions.outlet_id')
            ->whereIn('outlets.territory_id', $territories->getCollection()->pluck('id'))
            ->groupBy('outlets.territory_id')
            ->get()
            ->keyBy('territory_id');

        foreach ($territories as $territory) {
            $territory->tx_count = (int) ($txStats[$territory->id]->tx_count ?? 0);
            $territory->tx_total = (int) ($txStats[$territory->id]->tx_total ?? 0);
        }

        return view('developer.wilayah', compact('metrics', 'territories'));
    }

    public function show(Territory $territory): View
    {
        $territory->loadCount(['distributorProfiles', 'outlets']);

        $distributors = $territory->distributorProfiles()
            ->with(['user.profile'])
            ->get();

        $outlets = $territory->outlets()
            ->with('distributor')
            ->latest()
            ->paginate(10);

        $transactions = Transaction::whereHas('outlet', fn ($q) => $q->where('territory_id', $territory->id))
            ->with(['outlet', 'distributor'])
            ->latest()
            ->take(5)
            ->get();

        $visits = Visit::whereHas('outlet', fn ($q) => $q->where('territory_id', $territory->id))
            ->with(['outlet', 'distributor'])
            ->latest()
            ->take(5)
            ->get();

        $activity = ActivityLog::with('actor')
            ->where(fn ($q) => $q
                ->where('action', 'distributor.territory_assigned')
                ->orWhere(fn ($q2) => $q2
                    ->where('entity', Territory::class)
                    ->where('entity_id', $territory->id)))
            ->latest()
            ->take(30)
            ->get()
            ->filter(fn ($log) => ($log->entity === Territory::class && (int) $log->entity_id === (int) $territory->id)
                || ($log->action === 'distributor.territory_assigned'
                    && (int) ($log->metadata['territory_id'] ?? 0) === (int) $territory->id))
            ->take(10);

        // Coverage deterministik (selaras API peta): covered = outlet aktif + aktivitas ≤30 hari.
        $since = now()->subDays(30);
        $outletTotal = Outlet::where('territory_id', $territory->id)->count();
        $activeOutletCount = Outlet::where('territory_id', $territory->id)->where('status', 'active')->count();
        $hasDistributor = $territory->distributorProfiles()->exists();
        $recentActivity = Transaction::whereHas('outlet', fn ($q) => $q->where('territory_id', $territory->id))
                ->where('sold_at', '>=', $since)->exists()
            || Visit::whereHas('outlet', fn ($q) => $q->where('territory_id', $territory->id))
                ->where('visited_at', '>=', $since)->exists();
        $coverage = (! $hasDistributor || $outletTotal === 0)
            ? 'uncovered'
            : (($activeOutletCount > 0 && $recentActivity) ? 'covered' : 'partial');
        $txTotal = (int) Transaction::whereHas('outlet', fn ($q) => $q->where('territory_id', $territory->id))->sum('total_amount');
        $visitTotal = (int) Visit::whereHas('outlet', fn ($q) => $q->where('territory_id', $territory->id))->count();

        return view('developer.wilayah-detail', compact(
            'territory',
            'distributors',
            'outlets',
            'transactions',
            'visits',
            'activity',
            'coverage',
            'outletTotal',
            'activeOutletCount',
            'txTotal',
            'visitTotal'
        ));
    }
}
