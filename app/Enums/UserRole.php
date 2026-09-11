<?php

namespace App\Enums;

enum UserRole: string
{
    case Developer = 'developer';
    case Distributor = 'distributor';

    public function label(): string
    {
        return match ($this) {
            self::Developer => 'Developer',
            self::Distributor => 'Distributor',
        };
    }

    public function isDeveloper(): bool
    {
        return $this === self::Developer;
    }

    public function isDistributor(): bool
    {
        return $this === self::Distributor;
    }
}
