<?php

namespace App\Services\Providers;

use App\Services\Contracts\MapProviderInterface;
use App\Services\UnresolvableMapsLinkException;
use Illuminate\Support\Facades\Http;

class GoogleMapsProvider implements MapProviderInterface
{
    public const MAX_REDIRECTS = 5;

    public function name(): string
    {
        return 'google';
    }

    public function isConfigured(): bool
    {
        return (string) config('map.js_api_key', '') !== '';
    }

    /**
     * Resolusi short-link manual (tanpa mengandalkan handlerStats):
     * ikuti header Location hop-per-hop dengan validasi tiap hop.
     *
     * Keamanan (anti-SSRF): hanya skema http/https, hanya host Google
     * yang diizinkan, tolak IP literal privat/terlarang serta DNS yang
     * mengarah ke range privat. Bukan fetcher URL arbitrary.
     *
     * @throws UnresolvableMapsLinkException
     */
    public function resolveShortUrl(string $url): string
    {
        $current = trim($url);
        $this->assertResolvableStart($current);

        $timeout = (int) config('map.resolve_timeout', 8);

        for ($hop = 0; $hop < self::MAX_REDIRECTS; $hop++) {
            try {
                $response = Http::timeout($timeout)
                    ->withOptions(['allow_redirects' => false])
                    ->get($current);
            } catch (\Throwable) {
                throw UnresolvableMapsLinkException::unreadable();
            }

            $status = $response->status();

            if (! in_array($status, [301, 302, 303, 307, 308], true)) {
                // Bukan redirect lagi: anggap ini URL final, validasi lalu kembalikan.
                $this->assertFinalUrl($current);
                return $current;
            }

            $location = trim((string) $response->header('Location'));
            if ($location === '') {
                throw UnresolvableMapsLinkException::unreadable();
            }

            $current = $this->resolveAgainst($current, $location);
            $this->assertAllowedHop($current);
        }

        throw UnresolvableMapsLinkException::unreadable();
    }

    private function assertResolvableStart(string $url): void
    {
        $parts = parse_url($url);
        if (! is_array($parts)
            || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || empty($parts['host'])
        ) {
            throw UnresolvableMapsLinkException::unreadable();
        }
        $this->assertAllowedHop($url);
    }

    private function assertFinalUrl(string $url): void
    {
        $parts = parse_url($url);
        if (! is_array($parts)
            || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || empty($parts['host'])
        ) {
            throw UnresolvableMapsLinkException::unreadable();
        }
        // Final boleh Google Maps mana pun yang didukung parser.
        if (! \App\Services\GoogleMapsLinkParser::isGoogleHost($parts['host'])) {
            throw UnresolvableMapsLinkException::unreadable();
        }
    }

    /** Validasi tiap hop redirect: skema + host + bukan IP privat. */
    private function assertAllowedHop(string $url): void
    {
        $parts = parse_url($url);

        if (! is_array($parts)
            || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || empty($parts['host'])
            || ! \App\Services\GoogleMapsLinkParser::isGoogleHost($parts['host'])
        ) {
            throw UnresolvableMapsLinkException::unreadable();
        }

        $host = strtolower($parts['host']);

        // Tolak IP literal (v4/v6) — Google tidak pernah redirect ke IP.
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            throw UnresolvableMapsLinkException::unreadable();
        }

        // Tolak hostname yang me-resolve ke IP privat/terlarang (fail closed).
        $ips = @gethostbynamel($host);
        if ($ips === false) {
            throw UnresolvableMapsLinkException::unreadable();
        }
        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                throw UnresolvableMapsLinkException::unreadable();
            }
        }
    }

    /** Gabungkan Location relatif terhadap URL saat ini. */
    private function resolveAgainst(string $base, string $location): string
    {
        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }

        $parts = parse_url($base);
        $origin = strtolower($parts['scheme'] ?? 'https').'://'.strtolower($parts['host'] ?? '');

        if (str_starts_with($location, '/')) {
            return $origin.$location;
        }

        $path = $parts['path'] ?? '/';
        $dir = substr($path, 0, strrpos($path, '/') + 1);

        return $origin.$dir.$location;
    }
}
