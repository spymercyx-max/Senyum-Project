<?php

namespace App\Services;

use App\Models\Territory;
use Illuminate\Support\Str;

class TerritoryService
{
    public function normalize(string $value): string
    {
        $value = trim($value);
        $value = (string) Str::of($value)->lower();
        $value = (string) preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    public static function normalizeCity(string $city): string
    {
        return (new self)->normalize($city);
    }

    public static function normalizeDistrict(string $district): string
    {
        return (new self)->normalize($district);
    }

    public function findOrCreate(string $city, string $district, array $extra = []): Territory
    {
        $city = trim($city);
        $district = trim($district);

        return Territory::firstOrCreate(
            [
                'city_normalized' => $this->normalize($city),
                'district_normalized' => $this->normalize($district),
            ],
            array_merge([
                'city' => $city,
                'district' => $district,
                'status' => 'active',
            ], $extra)
        );
    }
}
