<?php

namespace App\Livewire\Distributor;

use App\Services\DistributorStockService;
use Livewire\Component;

class StockBoard extends Component
{
    public string $q = '';

    public function render(DistributorStockService $stocks)
    {
        $user = auth()->user();
        $q = trim($this->q);

        $rows = $user ? $stocks->allFor($user) : [];
        $lines = [];
        foreach ($rows as $row) {
            if (! $row['product']) {
                continue;
            }
            if ($q !== '' && stripos((string) $row['product']->name, $q) === false) {
                continue;
            }
            $lines[] = ['product' => $row['product'], 'qty' => (int) $row['qty']];
        }

        usort($lines, fn ($a, $b) => strcmp((string) $a['product']->name, (string) $b['product']->name));
        $lines = array_slice($lines, 0, 20);

        $totalUnits = $user ? $stocks->totalUnits($user) : 0;

        return view('livewire.distributor.stock-board', [
            'lines' => $lines,
            'totalUnits' => $totalUnits,
        ]);
    }
}
