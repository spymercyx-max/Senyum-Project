<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Outlet;
use App\Models\Territory;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Models\Visit;
use App\Services\MapService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $salesPerProduct = TransactionItem::selectRaw('product_id, SUM(qty) as total_qty, SUM(subtotal) as total_revenue, COUNT(*) as tx_lines')
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->get();

        $txByTerritory = Transaction::selectRaw('outlets.territory_id, COUNT(*) as tx_count, COALESCE(SUM(transactions.total_amount), 0) as tx_total')
            ->join('outlets', 'outlets.id', '=', 'transactions.outlet_id')
            ->groupBy('outlets.territory_id')
            ->get()
            ->keyBy('territory_id');

        $visitByTerritory = Visit::selectRaw('outlets.territory_id, COUNT(*) as visit_count')
            ->join('outlets', 'outlets.id', '=', 'visits.outlet_id')
            ->groupBy('outlets.territory_id')
            ->get()
            ->keyBy('territory_id');

        $territoryActivity = Territory::withCount('outlets')
            ->orderBy('city')
            ->orderBy('district')
            ->get()
            ->map(fn ($t) => [
                'territory' => $t,
                'outlets' => $t->outlets_count,
                'transactions' => (int) ($txByTerritory[$t->id]->tx_count ?? 0),
                'revenue' => (int) ($txByTerritory[$t->id]->tx_total ?? 0),
                'visits' => (int) ($visitByTerritory[$t->id]->visit_count ?? 0),
            ])
            ->sortByDesc(fn ($row) => $row['transactions'])
            ->values();

        $distributorPerformance = User::where('role', 'distributor')
            ->with(['profile', 'distributorProfile.territory'])
            ->withCount('transactions')
            ->withSum('transactions', 'total_amount')
            ->orderByDesc('transactions_sum_total_amount')
            ->take(10)
            ->get();

        $outletPerformance = Outlet::with(['territory', 'distributor'])
            ->withCount('transactions')
            ->withSum('transactions', 'total_amount')
            ->orderByDesc('transactions_sum_total_amount')
            ->take(10)
            ->get();

        $inventoryMovement = ActivityLog::with('actor')
            ->where('action', 'like', 'inventory.%')
            ->latest()
            ->take(20)
            ->get();

        return view('developer.laporan', compact(
            'salesPerProduct',
            'territoryActivity',
            'distributorPerformance',
            'outletPerformance',
            'inventoryMovement'
        ));
    }

    public function map(MapService $maps): View
    {
        return view('developer.peta', [
            'mapsKey' => $maps->browserKey(),
            'center' => $maps->defaultCenter(),
            'apiBase' => '/api/developer/map',
        ]);
    }
}
