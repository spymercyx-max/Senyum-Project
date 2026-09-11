<?php

return [
    'brand' => 'SENYUM',
    'brand_full' => 'KRETEK SENYUM',
    'whatsapp_number' => env('SENYUM_WA', '628980506754'),
    'developer_whatsapp' => env('SENYUM_DEV_WA', '6281222226989'),
    'site_title' => env('SENYUM_SITE_TITLE', 'KRETEK SENYUM 2.0'),
    'site_description' => env('SENYUM_SITE_DESC', 'Kretek khas dengan cita rasa senyum.'),
    'responsible_notice' => 'Produk khusus dewasa 18+. Merokok berisiko bagi kesehatan.',
    // Retensi data soft-deleted sebelum purge permanen (hari). Default 60 = 2 bulan.
    'purge_retention_days' => (int) env('SENYUM_PURGE_RETENTION_DAYS', 60),
];
