<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun Developer untuk development (idempoten).
 * Password default dari DEV_PASSWORD agar mudah diganti per environment.
 */
class DeveloperSeeder extends Seeder
{
    public function run(): void
    {
        $password = (string) (env('DEV_PASSWORD', 'fullsenyum') ?: 'fullsenyum');

        foreach ([
            ['name' => 'X-Mercy', 'username' => 'X-Mercy', 'email' => 'x-mercy@senyum.local'],
            ['name' => 'Madcapone', 'username' => 'Madcapone', 'email' => 'madcapone@senyum.local'],
            ['name' => 'Ahmadalkaff', 'username' => 'Ahmadalkaff', 'email' => 'ahmadalkaff@senyum.local'],
        ] as $row) {
            User::updateOrCreate(
                ['username' => $row['username']],
                [
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'phone' => null,
                    'password' => $password,
                    'role' => 'developer',
                    'status' => 'active',
                ]
            );
        }
    }
}
