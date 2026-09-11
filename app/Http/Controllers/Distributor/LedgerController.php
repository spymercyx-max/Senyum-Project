<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\DistributorStockService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pembukuan: ringkasan stok & penjualan milik distributor.
 * Tidak ada lagi perhitungan konsinyasi ("Sisa Titipan" dihapus).
 * "Jumlah Barang Tersedia" = seluruh unit di stok distributor
 * per produk + total (dari DistributorStockService, data nyata).
 */
class LedgerController extends Controller
{
    public function index(Request $request, DistributorStockService $stocks): View
    {
        $user = $request->user();

        $rows = $stocks->allFor($user);
        $lines = [];
        foreach ($rows as $row) {
            if (! $row['product']) {
                continue;
            }
            $lines[] = ['product' => $row['product'], 'qty' => $row['qty']];
        }

        $summary = [
            'stockLines' => $lines,
            'totalUnits' => $stocks->totalUnits($user),
            'totalTrx' => (int) $user->transactions()->sum('total_amount'),
            'trxMonth' => $user->transactions()->where('sold_at', '>=', now()->startOfMonth())->count(),
            'trx7' => $user->transactions()->where('sold_at', '>=', now()->subDays(7))->count(),
            'poActive' => $user->purchaseOrders()->whereNotIn('status', ['selesai', 'ditolak'])->count(),
        ];

        $recentTrx = $user->transactions()->with('outlet')->latest('sold_at')->limit(5)->get();
        $recentPo = $user->purchaseOrders()->latest()->limit(5)->get();
        $products = Product::active()->orderBy('name')->get();

        return view('distributor.pembukuan', [
            'summary' => $summary,
            'recentTrx' => $recentTrx,
            'recentPo' => $recentPo,
            'products' => $products,
            'territory' => $user->distributorProfile?->territory?->displayName(),
        ]);
    }
}
