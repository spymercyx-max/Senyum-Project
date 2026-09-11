<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\FeaturedProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturedProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_b_featured_unfeatures_a(): void
    {
        $a = Product::factory()->create(['status' => 'active', 'featured' => true]);
        $b = Product::factory()->create(['status' => 'active', 'featured' => false]);

        app(FeaturedProductService::class)->setFeatured($b);

        $this->assertFalse($a->refresh()->featured);
        $this->assertTrue($b->refresh()->featured);
        $this->assertSame(1, Product::where('featured', true)->count());
    }

    public function test_clear_featured(): void
    {
        Product::factory()->create(['status' => 'active', 'featured' => true]);

        app(FeaturedProductService::class)->clearFeatured();

        $this->assertSame(0, Product::where('featured', true)->count());
    }
}
