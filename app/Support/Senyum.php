<?php

namespace App\Support;

use App\Enums\StockStatus;

class Senyum
{
    public static function brand(): string
    {
        return (string) config('senyum.brand', 'SENYUM');
    }

    public static function formatRupiah(int|float $amount, bool $withPrefix = true): string
    {
        $formatted = number_format((float) $amount, 0, ',', '.');

        return $withPrefix ? 'Rp' . $formatted : $formatted;
    }

    public static function stockBadge(string|StockStatus $status): string
    {
        $value = $status instanceof StockStatus ? $status->value : strtolower((string) $status);

        return match ($value) {
            'healthy' => 'bg-green-100 text-green-800',
            'low' => 'bg-yellow-100 text-yellow-800',
            'critical' => 'bg-orange-100 text-orange-800',
            'out' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
