<?php

namespace App\Enums;

/** Mode pemenuhan Purchase Order. */
enum PoFulfillment: string
{
    case Delivery = 'delivery';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::Delivery => 'DIKIRIM',
            self::Pickup => 'PICK-UP / DIAMBIL',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Delivery => 'DIKIRIM',
            self::Pickup => 'DIAMBIL',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
