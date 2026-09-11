<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DistributorProfile;
use App\Models\Inventory;
use App\Models\Outlet;
use App\Models\PurchaseOrder;
use App\Models\Territory;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Visit;
use App\Services\InventoryService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(InventoryService $inventoryService): View
    {
        // 1) PO DRAFT menunggu persetujuan — prioritas utama.
        $pendingPOs = PurchaseOrder::with(['distributor', 'items'])
            ->where('status', 'draft')
            ->latest()
            ->take(8)
            ->get();
        $pendingPOCount = PurchaseOrder::where('status', 'draft')->count();

        // 2) Peringatan inventory: low / critical / out.
        $inventories = Inventory::with('product')->get();
        $alerts = $inventories
            ->filter(fn ($inv) => in_array($inventoryService->stockStatus($inv), ['low', 'critical', 'out'], true))
            ->sortBy(fn ($inv) => $inv->available)
            ->take(8)
            ->values();
        $alertCount = $inventories
            ->filter(fn ($inv) => in_array($inventoryService->stockStatus($inv), ['low', 'critical', 'out'], true))
            ->count();

        // 3) Registrasi distributor baru menunggu (pending).
        $pendingDistributors = User::where('role', 'distributor')
            ->whereHas('distributorProfile', fn ($q) => $q->where('status', 'pending'))
            ->with(['profile', 'distributorProfile.territory'])
            ->orderByDesc('created_at')
            ->take(8)
            ->get();
        $pendingDistributorCount = User::where('role', 'distributor')
            ->whereHas('distributorProfile', fn ($q) => $q->where('status', 'pending'))
            ->count();

        // 4) Pertumbuhan territory (30 hari terakhir) + 5 teratas.
        $newTerritories = Territory::where('created_at', '>=', now()->subDays(30))->count();

        $topTerritories = Territory::withCount(['outlets', 'distributorProfiles'])
            ->orderByDesc('outlets_count')
            ->orderBy('city')
            ->take(5)
            ->get();

        $txStats = Transaction::selectRaw('outlets.territory_id, COUNT(*) as tx_count, COALESCE(SUM(transactions.total_amount), 0) as tx_total')
            ->join('outlets', 'outlets.id', '=', 'transactions.outlet_id')
            ->groupBy('outlets.territory_id')
            ->get()
            ->keyBy('territory_id');

        foreach ($topTerritories as $territory) {
            $territory->tx_count = (int) ($txStats[$territory->id]->tx_count ?? 0);
            $territory->tx_total = (int) ($txStats[$territory->id]->tx_total ?? 0);
        }

        // 5) Coverage ringkas: covered = outlet aktif + aktivitas <=30 hari.
        $since = now()->subDays(30);
        $territoryIds = Territory::pluck('id');
        $covered = 0;
        $partial = 0;
        $uncovered = 0;
        foreach ($territoryIds as $id) {
            $outletCount = Outlet::where('territory_id', $id)->count();
            $activeCount = Outlet::where('territory_id', $id)->where('status', 'active')->count();
            $hasDistributor = DistributorProfile::where('territory_id', $id)->exists();

            if (! $hasDistributor || $outletCount === 0) {
                $uncovered++;
                continue;
            }

            $recent = Transaction::whereHas('outlet', fn ($q) => $q->where('territory_id', $id))
                    ->where('sold_at', '>=', $since)->exists()
                || Visit::whereHas('outlet', fn ($q) => $q->where('territory_id', $id))
                    ->where('visited_at', '>=', $since)->exists();

            if ($activeCount > 0 && $recent) {
                $covered++;
            } else {
                $partial++;
            }
        }
        $coverageTotal = max(1, $territoryIds->count());
        $coverage = [
            'covered' => $covered,
            'partial' => $partial,
            'uncovered' => $uncovered,
            'rate' => round($covered / $coverageTotal * 100, 1),
            'total' => $territoryIds->count(),
        ];

        $recentActivity = ActivityLog::with('actor')
            ->latest()
            ->take(10)
            ->get();

        return view('developer.dashboard', compact(
            'pendingPOs',
            'pendingPOCount',
            'alerts',
            'alertCount',
            'pendingDistributors',
            'pendingDistributorCount',
            'newTerritories',
            'topTerritories',
            'coverage',
            'recentActivity'
        ));
    }
}
