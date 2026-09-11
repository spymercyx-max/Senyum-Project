<?php

namespace App\Services;

class UnresolvableMapsLinkException extends \InvalidArgumentException
{
    public static function unreadable(): self
    {
        return new self('Link Google Maps tidak dapat dibaca. Silakan gunakan link lokasi Google Maps yang valid.');
    }

    public static function invalidCoordinates(): self
    {
        return new self('Koordinat pada link Google Maps tidak valid.');
    }
}

/**
 * Parser murni (tanpa I/O) untuk link Google Maps → koordinat.
 * Titik Google Maps yang diberikan user adalah lokasi otoritatif —
 * tidak pernah ditebak dari kota/kecamatan/alamat.
 *
 * ATURAN PEMILIHAN KOORDINAT (deterministik):
 * URL Place Google Maps sering memuat DUA pasangan koordinat:
 *   1. @LAT,LNG  → posisi KAMERA peta (bisa bergeser dari titik).
 *   2. !3dLAT!4dLNG → koordinat TEMPAT/OBJEK yang dibuka.
 * Karena kamera ≠ posisi outlet, prioritasnya:
 *   !3d/!4d (place)  →  @lat,lng (kamera/pin berbagi)  →  ?q/?query/
 *   ?destination  →  pola /dir/  →  parameter lat/lng.
 * Link berbagi pin (tanpa segmen place) hanya punya @lat,lng —
 * itulah titik sebenarnya dan tetap dipakai.
 *
 * Presisi dipertahankan penuh (float); database decimal(10,7).
 */
class GoogleMapsLinkParser
{
    /**
     * @return array{lat: float, lng: float}
     *
     * @throws UnresolvableMapsLinkException
     */
    public function parse(string $url): array
    {
        $url = trim($url);

        if ($url === '' || ! str_starts_with(strtolower($url), 'http')) {
            throw UnresolvableMapsLinkException::unreadable();
        }

        $coords = $this->extractFromData($url)
            ?? $this->extractFromAt($url)
            ?? $this->extractFromQuery($url)
            ?? $this->extractFromDir($url)
            ?? $this->extractFromLatLngParams($url);

        if ($coords === null) {
            throw UnresolvableMapsLinkException::unreadable();
        }

        return $this->validate($coords[0], $coords[1]);
    }

    /** Host Google yang didukung (untuk gate domain + anti-SSRF). */
    public static function isGoogleHost(?string $host): bool
    {
        $host = strtolower(trim((string) $host));
        if ($host === '') {
            return false;
        }

        return $host === 'goo.gl'
            || str_ends_with($host, '.goo.gl')
            || $host === 'google.com'
            || str_ends_with($host, '.google.com')
            || $host === 'g.co'
            || str_ends_with($host, '.g.co');
    }

    /** URL didukung = short-link atau host Google Maps. */
    public function isSupported(string $url): bool
    {
        $parts = parse_url(trim($url));
        if (! is_array($parts)
            || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || empty($parts['host'])
        ) {
            return false;
        }

        return self::isGoogleHost($parts['host']);
    }

    /** Segmen data=!...!3dLAT!4dLNG pada URL place = koordinat TEMPAT. */
    private function extractFromData(string $url): ?array
    {
        if (preg_match('/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/', $url, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }
        return null;
    }

    /** Pola @lat,lng,zoom — kamera peta / pin berbagi. */
    private function extractFromAt(string $url): ?array
    {
        if (preg_match('/@(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)(?:,|\/|$|\?)/', $url, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }
        return null;
    }

    /** ?q=lat,lng / ?query=lat,lng / ?destination=lat,lng / ?daddr= / ?ll= */
    private function extractFromQuery(string $url): ?array
    {
        $parts = parse_url($url);
        if (! isset($parts['query'])) {
            return null;
        }
        parse_str($parts['query'], $q);
        foreach (['q', 'query', 'destination', 'daddr', 'saddr', 'll'] as $key) {
            if (isset($q[$key]) && is_string($q[$key])
                && preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $q[$key], $m)) {
                return [(float) $m[1], (float) $m[2]];
            }
        }
        return null;
    }

    /** /dir/lat,lng/... atau /dir/.../lat,lng */
    private function extractFromDir(string $url): ?array
    {
        if (preg_match('#/dir/(?:[^/]+/)*(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)(?:/|$|\?)#', $url, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }
        return null;
    }

    /** &lat=..&lng=.. / &lat=..&lon=.. (embed & beberapa format). */
    private function extractFromLatLngParams(string $url): ?array
    {
        $parts = parse_url($url);
        if (! isset($parts['query'])) {
            return null;
        }
        parse_str($parts['query'], $q);
        $lat = $q['lat'] ?? $q['latitude'] ?? null;
        $lng = $q['lng'] ?? $q['lon'] ?? $q['long'] ?? $q['longitude'] ?? null;
        if (is_numeric($lat) && is_numeric($lng)) {
            return [(float) $lat, (float) $lng];
        }
        return null;
    }

    /** @return array{lat: float, lng: float} */
    private function validate(float $lat, float $lng): array
    {
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            throw UnresolvableMapsLinkException::invalidCoordinates();
        }
        if ($lat == 0.0 && $lng == 0.0) {
            throw UnresolvableMapsLinkException::invalidCoordinates();
        }
        return ['lat' => $lat, 'lng' => $lng];
    }

    public function isShortLink(string $url): bool
    {
        // Hanya domain pemendek sungguhan. maps.google.com adalah host
        // kanonis (mis. https://maps.google.com/?q=..) — bukan short link.
        $host = strtolower((string) parse_url(trim($url), PHP_URL_HOST));
        return $host === 'maps.app.goo.gl'
            || $host === 'goo.gl'
            || str_ends_with($host, '.goo.gl');
    }
}
