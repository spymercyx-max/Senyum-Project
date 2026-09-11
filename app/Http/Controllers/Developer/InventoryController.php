<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Inventory;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class InventoryController extends Controller
{
    public function index(Request $request, InventoryService $service): View
    {
        $status = $request->query('status');
        $allowed = ['healthy', 'low', 'critical', 'out'];
        if (! in_array($status, $allowed, true)) {
            $status = null;
        }

        $all = Inventory::with('product')->get();

        $counts = [
            'all' => $all->count(),
            'healthy' => 0,
            'low' => 0,
            'critical' => 0,
            'out' => 0,
        ];

        foreach ($all as $inventory) {
            $key = $service->stockStatus($inventory);
            if (isset($counts[$key])) {
                $counts[$key]++;
            }
        }

        $inventories = $all
            ->when($status, fn ($c) => $c->filter(fn ($inv) => $service->stockStatus($inv) === $status))
            ->sortBy(fn ($inv) => $inv->product?->name ?? '')
            ->values();

        return view('developer.inventory', compact('inventories', 'counts', 'status'));
    }

    public function adjust(Request $request, Inventory $inventory, InventoryService $service): RedirectResponse
    {
        $data = $request->validate([
            'delta' => ['required', 'integer', 'min:-1000000', 'max:1000000', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var Product $product */
        $product = $inventory->product;

        try {
            $service->adjust($product, (int) $data['delta'], $data['reason'] ?? null);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable) {
            return back()->with('error', 'Gagal menyesuaikan stok. Silakan coba lagi.');
        }

        ActivityLog::create([
            'actor_id' => auth()->id(),
            'action' => 'inventory.adjusted',
            'entity' => Inventory::class,
            'entity_id' => $inventory->id,
            'metadata' => [
                'product' => $product->name,
                'delta' => (int) $data['delta'],
                'reason' => $data['reason'] ?? null,
            ],
            'ip' => $request->ip(),
        ]);

        $arah = (int) $data['delta'] > 0 ? 'ditambah' : 'dikurangi';

        return back()->with('success', "Stok {$product->name} berhasil {$arah} " . abs((int) $data['delta']) . ' pcs.');
    }
}
