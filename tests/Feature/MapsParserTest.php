<?php

namespace Tests\Feature;

use App\Services\GoogleMapsLinkParser;
use App\Services\MapService;
use App\Services\UnresolvableMapsLinkException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MapsParserTest extends TestCase
{
    use RefreshDatabase;

    private GoogleMapsLinkParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = app(GoogleMapsLinkParser::class);
    }

    public function test_at_pattern_coordinate_url(): void
    {
        $r = $this->parser->parse('https://www.google.com/maps/@-6.1753924,106.8271528,17z');
        $this->assertEqualsWithDelta(-6.1753924, $r['lat'], 0.0000001);
        $this->assertEqualsWithDelta(106.8271528, $r['lng'], 0.0000001);
    }

    public function test_place_url_with_data_segment(): void
    {
        $r = $this->parser->parse('https://www.google.com/maps/place/Monas/data=!3m1!4b1!4m6!3m5!1s0x123!8m2!3d-6.1753924!4d106.8271528!16z');
        $this->assertEqualsWithDelta(-6.1753924, $r['lat'], 0.0000001);
        $this->assertEqualsWithDelta(106.8271528, $r['lng'], 0.0000001);
    }

    public function test_query_pattern(): void
    {
        $r = $this->parser->parse('https://maps.google.com/?q=-7.2575,112.7521');
        $this->assertEqualsWithDelta(-7.2575, $r['lat'], 0.0000001);
        $this->assertEqualsWithDelta(112.7521, $r['lng'], 0.0000001);
    }

    public function test_invalid_url_rejected_with_user_message(): void
    {
        try {
            $this->parser->parse('https://example.com/bukan-maps');
            $this->fail('Harusnya melempar exception.');
        } catch (UnresolvableMapsLinkException $e) {
            $this->assertSame('Link Google Maps tidak dapat dibaca. Silakan gunakan link lokasi Google Maps yang valid.', $e->getMessage());
        }
    }

    public function test_zero_zero_and_out_of_range_rejected(): void
    {
        foreach (['https://www.google.com/maps/@0,0,10z', 'https://www.google.com/maps/@-95,200,10z'] as $url) {
            try {
                $this->parser->parse($url);
                $this->fail("Harusnya menolak: {$url}");
            } catch (UnresolvableMapsLinkException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_short_link_resolved_then_parsed(): void
    {
        $this->assertTrue($this->parser->isShortLink('https://maps.app.goo.gl/abcXYZ'));
        $this->assertFalse($this->parser->isShortLink('https://www.google.com/maps/@-6.1,106.8,12z'));

        // Provider stub: short link → URL kanonis berkoordinat.
        app()->bind(\App\Services\Contracts\MapProviderInterface::class, fn () => new class implements \App\Services\Contracts\MapProviderInterface {
            public function name(): string { return 'stub'; }
            public function isConfigured(): bool { return false; }
            public function resolveShortUrl(string $url): string {
                return 'https://www.google.com/maps/@-6.1754,106.8272,17z';
            }
        });

        $r = app(MapService::class)->coordinatesFromLink('https://maps.app.goo.gl/abcXYZ');
        $this->assertEqualsWithDelta(-6.1754, $r['lat'], 0.0000001);
        $this->assertEqualsWithDelta(106.8272, $r['lng'], 0.0000001);
    }

    public function test_exact_user_short_url_resolves_to_place_coordinates(): void
    {
        // Rantai redirect yang-remipakan perilaku Google untuk short URL user.
        Http::fake([
            'maps.app.goo.gl/*' => Http::response('', 302, [
                'Location' => 'https://www.google.com/maps/place/OKSIGEN+24/@-7.9393237,112.6245966,17z/data=!3m1!8m2!3d-7.9390937!4d112.6247288',
            ]),
            'www.google.com/*' => Http::response('', 200),
        ]);

        $r = app(MapService::class)->coordinatesFromLink('https://maps.app.goo.gl/ARaPDYhML3oHWw549');

        // BUKAN kamera (@-7.9393237,112.6245966), melainkan TEMPAT (!3d/!4d).
        $this->assertEqualsWithDelta(-7.9390937, $r['lat'], 0.0000001);
        $this->assertEqualsWithDelta(112.6247288, $r['lng'], 0.0000001);
    }

    public function test_short_url_redirect_failure_is_safe_error(): void
    {
        Http::fake([
            'maps.app.goo.gl/*' => Http::response('gone', 404),
            'www.google.com/*' => Http::response('', 200),
        ]);

        try {
            app(MapService::class)->coordinatesFromLink('https://maps.app.goo.gl/ARaPDYhML3oHWw549');
            $this->fail('Redirect gagal harus melempar error aman.');
        } catch (UnresolvableMapsLinkException $e) {
            $this->assertSame('Link Google Maps tidak dapat dibaca. Silakan gunakan link lokasi Google Maps yang valid.', $e->getMessage());
        }
    }

    public function test_malicious_redirect_target_rejected_ssrf(): void
    {
        Http::fake([
            'maps.app.goo.gl/*' => Http::response('', 302, ['Location' => 'http://169.254.169.254/latest/meta-data/']),
        ]);

        try {
            app(MapService::class)->coordinatesFromLink('https://maps.app.goo.gl/ARaPDYhML3oHWw549');
            $this->fail('Target privat harus ditolak.');
        } catch (UnresolvableMapsLinkException) {
            $this->assertTrue(true);
        }
    }

    public function test_non_google_domain_rejected_without_http(): void
    {
        Http::preventStrayRequests();

        try {
            app(MapService::class)->coordinatesFromLink('https://example.com/maps/@-6.1,106.8,12z');
            $this->fail('Domain non-Google harus ditolak.');
        } catch (UnresolvableMapsLinkException) {
            $this->assertTrue(true);
        }

        Http::assertNothingSent();
    }

    public function test_multi_pair_place_preferred_over_camera(): void
    {
        $url = 'https://www.google.com/maps/place/Contoh/@-7.9393237,112.6245966,17z/data=!3m1!8m2!3d-7.9390937!4d112.6247288';
        $r = $this->parser->parse($url);
        $this->assertEqualsWithDelta(-7.9390937, $r['lat'], 0.0000001);
        $this->assertEqualsWithDelta(112.6247288, $r['lng'], 0.0000001);
    }

    public function test_destination_and_dir_patterns(): void
    {
        $r = $this->parser->parse('https://www.google.com/maps/dir/?api=1&destination=-6.2,106.8');
        $this->assertEqualsWithDelta(-6.2, $r['lat'], 0.0000001);

        $r = $this->parser->parse('https://www.google.com/maps/dir/Jakarta/-6.1754,106.8272');
        $this->assertEqualsWithDelta(-6.1754, $r['lat'], 0.0000001);
    }
    public function test_map_service_without_key_reports_unavailable(): void
    {
        config()->set('map.js_api_key', '');
        $service = app(MapService::class);

        $this->assertFalse($service->isInteractiveAvailable());
        $this->assertSame('', $service->browserKey());
    }
}
