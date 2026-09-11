<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_wa_url_generation(): void
    {
        $service = app(WhatsAppService::class);

        $url = $service->waUrl('0812-3456-7890', 'Halo Senyum');

        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $url);
        $this->assertStringContainsString(urlencode('Halo Senyum'), $url);
    }

    public function test_product_inquiry_url_contains_product_name(): void
    {
        $product = Product::factory()->create(['name' => 'Senyum Original 12', 'price' => 22000]);

        $url = app(WhatsAppService::class)->productInquiryUrl($product);

        $this->assertStringStartsWith('https://wa.me/', $url);
        $this->assertStringContainsString(urlencode('Senyum Original 12'), $url);
    }
}
