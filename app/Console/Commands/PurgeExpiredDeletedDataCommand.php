<?php

namespace App\Console\Commands;

use App\Models\Outlet;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Console\Command;

class PurgeExpiredDeletedDataCommand extends Command
{
    protected $signature = 'senyum:purge-expired-deleted-data
                            {--days= : Masa retensi hari (default dari config)}
                            {--dry-run : Tampilkan yang akan dihapus tanpa menghapus}';

    protected $description = 'Hapus permanen data soft-deleted yang melewati masa retensi (default 60 hari)';

    public function handle(): int
    {
        $optDays = $this->option('days');
        $days = $optDays !== null && $optDays !== '' ? (int) $optDays : (int) config('senyum.purge_retention_days', 60);
        $cutoff = now()->subDays($days);
        $dry = (bool) $this->option('dry-run');

        $targets = [
            'produk' => Product::onlyTrashed()->where('deleted_at', '<=', $cutoff),
            'outlet' => Outlet::onlyTrashed()->where('deleted_at', '<=', $cutoff),
            'transaksi' => Transaction::onlyTrashed()->where('deleted_at', '<=', $cutoff),
        ];

        $total = 0;
        foreach ($targets as $label => $query) {
            $count = (clone $query)->count();
            $total += $count;
            if ($dry) {
                $this->line("[dry-run] {$label}: {$count} akan dihapus permanen (deleted_at < {$cutoff->toDateString()}).");
            } else {
                (clone $query)->forceDelete();
                $this->info("{$label}: {$count} dihapus permanen.");
            }
        }

        $this->info($dry ? "Total kandidat: {$total}." : "Selesai. Total dihapus permanen: {$total}.");

        return 0;
    }
}
