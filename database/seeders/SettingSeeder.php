<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'brand_name', 'value' => 'Kretek Senyum', 'type' => 'string'],
            ['key' => 'whatsapp_number', 'value' => '628980506754', 'type' => 'string'],
            ['key' => 'developer_whatsapp', 'value' => '6281222226989', 'type' => 'string'],
            ['key' => 'site_title', 'value' => 'KRETEK SENYUM 2.0', 'type' => 'string'],
            ['key' => 'site_description', 'value' => 'Kretek khas dengan cita rasa senyum.', 'type' => 'string'],
            ['key' => 'responsible_notice', 'value' => 'Produk khusus dewasa 18+. Merokok berisiko bagi kesehatan.', 'type' => 'string'],
        ];

        foreach ($defaults as $row) {
            // Lewat Setting::set agar cache rememberForever ikut di-bust.
            Setting::set($row['key'], $row['value'], $row['type']);
        }
    }
}
