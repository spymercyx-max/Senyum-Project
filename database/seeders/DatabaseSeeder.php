<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            TerritorySeeder::class,
            DeveloperSeeder::class,
            DistributorSeeder::class,
            ProductSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
