<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $cached = Cache::rememberForever('setting:' . $key, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (! $cached) {
            return value($default);
        }

        return static::castValue($cached->value, $cached->type);
    }

    public static function set(string $key, mixed $value, string $type = 'string'): static
    {
        $stored = static::serializeValue($value, $type);

        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'type' => $type]
        );

        Cache::forget('setting:' . $key);

        return $setting;
    }

    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'int', 'integer' => (int) $value,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'float', 'double' => (float) $value,
            'array', 'json' => is_string($value) ? json_decode($value, true) : (array) $value,
            default => $value,
        };
    }

    protected static function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'array', 'json' => json_encode($value),
            'bool', 'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };
    }
}
