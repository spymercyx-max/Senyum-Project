<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\TerritoryService;
use Illuminate\Database\Seeder;

/**
 * Satu akun Distributor development.
 *
 * Keputusan auth (§7): users.username unik, satu halaman login tanpa
 * role selector → username developer "X-Mercy" dan distributor tidak
 * boleh sama. Distributor memakai "X-Mercy-Dist" (deterministik,
 * tanpa perilaku login ambigu); nama tampilan/profil tetap "X-Mercy".
 */
class DistributorSeeder extends Seeder
{
    public function run(): void
    {
        $password = (string) (env('DEV_PASSWORD', 'fullsenyum') ?: 'fullsenyum');
        $territory = app(TerritoryService::class)->findOrCreate('Malang', 'Klojen');

        $user = User::updateOrCreate(
            ['username' => 'X-Mercy-Dist'],
            [
                'name' => 'X-Mercy',
                'email' => 'x-mercy.dist@senyum.local',
                'phone' => '6281000000001',
                'password' => $password,
                'role' => 'distributor',
                'status' => 'active',
            ]
        );

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'city' => 'Malang',
                'district' => 'Klojen',
                'experience' => 'Akun development',
                'whatsapp' => '6281000000001',
            ]
        );

        $firstDev = User::where('role', 'developer')->orderBy('id')->value('id');

        $user->distributorProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'territory_id' => $territory->id,
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $firstDev,
            ]
        );
    }
}
