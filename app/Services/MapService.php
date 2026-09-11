<?php

namespace App\Services;

use App\Services\Contracts\MapProviderInterface;
use Illuminate\Support\Facades\Cache;

/**
 * Pintu masuk tunggal untuk kebutuhan peta:
 * link Google Maps (user) → koordinat kanonis tersimpan.
 *
 * Peta selalu memakai koordinat yang TERSIMPAN di database,
 * tidak pernah me-parse ulang URL saat render.
 */
class MapService
{
    public function __construct(
        protected GoogleMapsLinkParser $parser,
        protected MapProviderInterface $provider,
    ) {}

    /**
     * @return array{lat: float, lng: float, resolved_url: string}
     *
     * @throws UnresolvableMapsLinkException
     */
    public function coordinatesFromLink(string $url): array
    {
        $url = trim($url);

        // Tolak domain non-Google sebelum request apa pun (anti-SSRF +
        // "Non-Google URL harus ditolak").
        if (! $this->parser->isSupported($url)) {
            throw UnresolvableMapsLinkException::unreadable();
        }

        if ($this->parser->isShortLink($url)) {
            $url = $this->resolveCached($url);
        }

        $coords = $this->parser->parse($url);

        return $coords + ['resolved_url' => $url];
    }

    public function browserKey(): string
    {
        return (string) config('map.js_api_key', '');
    }

    public function isInteractiveAvailable(): bool
    {
        return $this->provider->isConfigured();
    }

    public function defaultCenter(): array
    {
        return [
            'lat' => (float) config('map.default_lat', -6.2),
            'lng' => (float) config('map.default_lng', 106.8),
            'zoom' => (int) config('map.default_zoom', 6),
        ];
    }

    private function resolveCached(string $url): string
    {
        $key = 'maps_resolve:'.sha1(strtolower($url));

        return Cache::remember($key, now()->addDays((int) config('map.resolve_cache_days', 30)), function () use ($url) {
            return $this->provider->resolveShortUrl($url);
        });
    }
}
