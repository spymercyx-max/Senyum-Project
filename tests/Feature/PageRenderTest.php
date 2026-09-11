<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render_with_brand_identity(): void
    {
        foreach (['/', '/tentang', '/produk', '/kemitraan', '/login', '/register'] as $url) {
            $response = $this->get($url);
            $response->assertOk();
            // SENYUM branding visible without reading title
            $response->assertSee('SENYUM', false);
        }
    }

    public function test_login_page_has_no_role_selector_and_no_demo(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
        $response->assertDontSee('Pilih Role', false);
        $response->assertDontSee('Select Role', false);
        $response->assertDontSee('Demo Account', false);
        $response->assertDontSee('demo account', false);
    }

    public function test_no_dark_mode_toggle_anywhere(): void
    {
        foreach (['/', '/login', '/register', '/produk'] as $url) {
            $response = $this->get($url);
            $response->assertDontSee('dark mode', false);
            $response->assertDontSee('Dark Mode', false);
            $response->assertDontSee('toggle-theme', false);
        }
    }

    public function test_home_contains_all_required_sections(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('Tentang Senyum', false);
        $response->assertSee('Komposisi', false);
        $response->assertSee('Karakter Senyum', false);
        $response->assertSee('Cara Memesan', false);
        $response->assertSee('Kemitraan Senyum', false);
        $response->assertSee('18+', false);
    }

    public function test_home_shows_max_three_products(): void
    {
        Product::factory()->count(5)->create(['status' => 'active']);

        $home = $this->get('/')->getContent();
        // Each homepage card links once to /produk/{slug}; nav uses plain /produk
        $this->assertSame(3, substr_count($home, '/produk/'));

        $catalog = $this->get('/produk')->getContent();
        $this->assertSame(5, substr_count($catalog, '/produk/'));
    }

    public function test_responsible_content_has_no_health_claims(): void
    {
        $content = $this->get('/')->getContent()
            . $this->get('/tentang')->getContent()
            . $this->get('/produk')->getContent();

        foreach (['lebih sehat', 'menyehatkan', 'sebagai obat', 'terapi rokok', 'detox'] as $claim) {
            $this->assertStringNotContainsStringIgnoringCase($claim, $content);
        }
    }
}
