<?php

return [
    'provider' => env('MAP_PROVIDER', 'google'),

    // Google Maps Platform — hanya yang benar-benar dipakai.
    // Tanpa key, halaman peta menampilkan failure state + daftar (tidak crash).
    'js_api_key' => env('GOOGLE_MAPS_API_KEY', env('GOOGLE_MAPS_MAPS_JS_API_KEY', '')),
    'geocoding_api_key' => env('GOOGLE_MAPS_GEOCODING_API_KEY', env('GOOGLE_MAPS_API_KEY', '')),
    'directions_api_key' => env('GOOGLE_MAPS_DIRECTIONS_API_KEY', env('GOOGLE_MAPS_API_KEY', '')),

    'default_lat' => env('MAP_DEFAULT_LAT', -6.2),
    'default_lng' => env('MAP_DEFAULT_LNG', 106.8),
    'default_zoom' => env('MAP_DEFAULT_ZOOM', 6),

    // Kontrol biaya: cache hasil resolusi short-link & geocoding.
    'resolve_timeout' => env('MAP_RESOLVE_TIMEOUT', 8),
    'resolve_cache_days' => env('MAP_RESOLVE_CACHE_DAYS', 30),
];
