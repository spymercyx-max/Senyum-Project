<?php

namespace App\Enums;

enum StockStatus: string
{
    case Healthy = 'healthy';
    case Low = 'low';
    case Critical = 'critical';
    case Out = 'out';

    public function label(): string
    {
        return match ($this) {
            self::Healthy => 'Sehat',
            self::Low => 'Rendah',
            self::Critical => 'Kritis',
            self::Out => 'Habis',
        };
    }
}
