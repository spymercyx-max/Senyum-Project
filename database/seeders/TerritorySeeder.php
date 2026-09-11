<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Clean start: territory terbentuk otomatis dari registrasi
 * distributor (TerritoryService::findOrCreate). Tidak ada
 * aktivitas territory palsu.
 */
class TerritorySeeder extends Seeder
{
    public function run(): void
    {
        // Sengaja kosong — lihat docs/database.md.
    }
}
