<?php

namespace App\Livewire\Distributor;

use App\Models\Outlet;
use App\Models\Product;
use App\Services\ProductPricingService;
use App\Services\TransactionService;
use InvalidArgumentException;
use Livewire\Component;

class OrderForm extends Component
{
    public string $outlet_id = '';

    public string $sold_at = '';

    /** @var array<int, array{product_id: mixed, qty: mixed, price: mixed}> */
    public array $rows = [];

    public string $notes = '';

    public ?string $success = null;

    public function mount(): void
    {
        $this->sold_at = today()->toDateString();
        $this->rows = [['product_id' => '', 'qty' => 1, 'price' => null]];
    }

    public function addRow(): void
    {
        $this->rows[] = ['product_id' => '', 'qty' => 1, 'price' => null];
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);

        if (empty($this->rows)) {
            $this->addRow();
        }
    }

    public function submit(TransactionService $service, ProductPricingService $pricing): void
    {
        $this->success = null;

        $validated = $this->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
            'sold_at' => ['nullable', 'date'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.product_id' => ['required', 'exists:products,id'],
            'rows.*.qty' => ['required', 'integer', 'min:1'],
            'rows.*.price' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ], [], [
            'outlet_id' => 'outlet',
            'sold_at' => 'tanggal',
            'rows.*.product_id' => 'produk',
            'rows.*.qty' => 'jumlah',
            'rows.*.price' => 'harga',
        ]);

        $user = auth()->user();
        $outlet = Outlet::findOrFail($validated['outlet_id']);

        if ((int) $outlet->distributor_id !== (int) $user->id) {
            $this->addError('outlet_id', 'Outlet ini bukan milik Anda.');

            return;
        }

        try {
            $trx = $service->createTransaction($user, $outlet, array_map(fn ($r) => [
                'product_id' => (int) $r['product_id'],
                'qty' => (int) $r['qty'],
                'price' => (($r['price'] ?? null) === '' || ($r['price'] ?? null) === null) ? null : (int) $r['price'],
            ], $validated['rows']), [
                'notes' => $validated['notes'] ?? null,
                'sold_at' => $validated['sold_at'] ?? now(),
            ]);
        } catch (InvalidArgumentException $e) {
            $this->addError('rows', $e->getMessage());

            return;
        }

        $this->success = "Pesanan {$trx->code} berhasil dicatat (Rp " . number_format($trx->total_amount, 0, ',', '.') . ").";
        $this->reset(['rows', 'notes']);
        $this->rows = [['product_id' => '', 'qty' => 1, 'price' => null]];
    }

    public function render(ProductPricingService $pricing)
    {
        $user = auth()->user();
        $outlets = $user ? $user->outlets()->where('status', 'active')->orderBy('name')->get() : collect();
        $products = Product::active()->with(['inventory', 'tiers'])->orderBy('name')->get();
        $distPrices = [];
        foreach ($products as $p) {
            $distPrices[$p->id] = $pricing->resolve($p, 'distributor', 1);
        }

        $rowSubtotals = [];
        $total = 0;
        foreach ($this->rows as $i => $row) {
            $sub = 0;
            if (! empty($row['product_id'])) {
                $unit = null;
                if (isset($row['price']) && $row['price'] !== '' && $row['price'] !== null) {
                    $unit = (int) $row['price'];
                } elseif (isset($distPrices[$row['product_id']])) {
                    $unit = (int) $distPrices[$row['product_id']];
                }
                if ($unit !== null) {
                    $sub = $unit * max(0, (int) ($row['qty'] ?? 0));
                }
            }
            $rowSubtotals[$i] = $sub;
            $total += $sub;
        }

        return view('livewire.distributor.order-form', [
            'outlets' => $outlets,
            'products' => $products,
            'distPrices' => $distPrices,
            'rowSubtotals' => $rowSubtotals,
            'total' => $total,
        ]);
    }
}
