<?php

namespace App\Services;

use App\Enums\StockStatus;
use App\Models\Inventory;
use App\Models\Product;
use InvalidArgumentException;

class InventoryService
{
    public function adjust(Product $product, int $delta, ?string $reason = null): Inventory
    {
        $inventory = $this->forProduct($product);
        $inventory->stock += $delta;

        if ($inventory->stock < 0) {
            throw new InvalidArgumentException('Stok tidak boleh negatif.');
        }

        $inventory->save();

        return $inventory->refresh();
    }

    public function reserve(Product $product, int $qty): Inventory
    {
        if ($qty < 1) {
            throw new InvalidArgumentException('Qty reservasi minimal 1.');
        }

        $inventory = $this->forProduct($product);

        if ($inventory->available < $qty) {
            throw new InvalidArgumentException('Stok tersedia tidak mencukupi.');
        }

        $inventory->reserved += $qty;
        $inventory->save();

        return $inventory->refresh();
    }

    public function release(Product $product, int $qty): Inventory
    {
        if ($qty < 1) {
            throw new InvalidArgumentException('Qty release minimal 1.');
        }

        $inventory = $this->forProduct($product);
        $inventory->reserved = max(0, $inventory->reserved - $qty);
        $inventory->save();

        return $inventory->refresh();
    }

    /**
     * Pengurangan dengan kunci baris — untuk approval PO.
     * Menolak bila stok tidak cukup (anti negatif, anti race).
     */
    public function deductLocked(Product $product, int $qty, ?string $reason = null): Inventory
    {
        if ($qty < 1) {
            throw new InvalidArgumentException('Qty pengurangan minimal 1.');
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($product, $qty) {
            $inventory = Inventory::where('product_id', $product->id)->lockForUpdate()->first()
                ?? $this->forProduct($product);

            if ((int) $inventory->stock - (int) $inventory->reserved < $qty) {
                throw new InvalidArgumentException(
                    "Stok pusat {$product->name} tidak mencukupi (tersedia {$inventory->available}, diminta {$qty})."
                );
            }

            $inventory->stock -= $qty;
            $inventory->save();

            return $inventory->refresh();
        });
    }

    public function forProduct(Product $product): Inventory
    {
        return Inventory::firstOrCreate(
            ['product_id' => $product->id],
            ['stock' => 0, 'reserved' => 0, 'threshold' => 10]
        );
    }

    /**
     * Klasifikasi status stok: healthy|low|critical|out
     */
    public function stockStatus(Inventory $inventory): string
    {
        $available = (int) $inventory->stock - (int) $inventory->reserved;
        $threshold = max(0, (int) $inventory->threshold);

        if ($available <= 0) {
            return StockStatus::Out->value;
        }

        if ($threshold <= 0) {
            return StockStatus::Healthy->value;
        }

        if ($available <= (int) ceil($threshold * 0.5)) {
            return StockStatus::Critical->value;
        }

        if ($available <= $threshold) {
            return StockStatus::Low->value;
        }

        return StockStatus::Healthy->value;
    }
}
