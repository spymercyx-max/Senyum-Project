<?php

namespace App\Livewire\Developer;

use App\Models\ActivityLog;
use App\Models\Inventory;
use App\Services\InventoryService;
use InvalidArgumentException;
use Livewire\Component;

class InventoryBoard extends Component
{
    public string $status = '';

    public ?int $adjustId = null;

    public ?int $delta = null;

    public ?string $reason = null;

    public ?string $notice = null;

    public ?string $error = null;

    public function openAdjust(int $inventoryId): void
    {
        $this->adjustId = $inventoryId;
        $this->delta = null;
        $this->reason = null;
        $this->error = null;
    }

    public function cancelAdjust(): void
    {
        $this->adjustId = null;
        $this->delta = null;
        $this->reason = null;
    }

    public function saveAdjust(InventoryService $service): void
    {
        $this->validate([
            'delta' => ['required', 'integer', 'min:-1000000', 'max:1000000', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $inventory = Inventory::with('product')->find($this->adjustId);

        if (! $inventory) {
            $this->error = 'Data inventory tidak ditemukan.';

            return;
        }

        try {
            $service->adjust($inventory->product, (int) $this->delta, $this->reason);
        } catch (InvalidArgumentException $e) {
            $this->error = $e->getMessage();

            return;
        } catch (\Throwable) {
            $this->error = 'Gagal menyesuaikan stok. Silakan coba lagi.';

            return;
        }

        ActivityLog::create([
            'actor_id' => auth()->id(),
            'action' => 'inventory.adjusted',
            'entity' => Inventory::class,
            'entity_id' => $inventory->id,
            'metadata' => [
                'product' => $inventory->product->name,
                'delta' => (int) $this->delta,
                'reason' => $this->reason,
            ],
            'ip' => request()->ip(),
        ]);

        $arah = (int) $this->delta > 0 ? 'ditambah' : 'dikurangi';
        $this->notice = "Stok {$inventory->product->name} berhasil {$arah} " . abs((int) $this->delta) . ' pcs.';
        $this->error = null;
        $this->cancelAdjust();
    }

    public function render(InventoryService $service)
    {
        $all = Inventory::with('product')->get();

        $counts = ['all' => $all->count(), 'healthy' => 0, 'low' => 0, 'critical' => 0, 'out' => 0];
        foreach ($all as $inv) {
            $key = $service->stockStatus($inv);
            if (isset($counts[$key])) {
                $counts[$key]++;
            }
        }

        $inventories = $all
            ->when($this->status !== '', fn ($c) => $c->filter(fn ($inv) => $service->stockStatus($inv) === $this->status))
            ->sortBy(fn ($inv) => $inv->product?->name ?? '')
            ->values();

        return view('livewire.developer.inventory-board', compact('inventories', 'counts'));
    }
}
