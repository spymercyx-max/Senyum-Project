<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public function get(string $key, mixed $default = null): mixed
    {
        $fallback = $default ?? config('senyum.' . $key);

        return Setting::get($key, $fallback);
    }

    public function set(string $key, mixed $value, string $type = 'string'): Setting
    {
        return Setting::set($key, $value, $type);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(array $keys, array $defaults = []): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $defaults[$key] ?? null);
        }

        return $result;
    }
}
