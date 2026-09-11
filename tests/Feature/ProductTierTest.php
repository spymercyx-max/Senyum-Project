<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\FeaturedProductService;
use App\Services\ProductPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ProductTierTest extends TestCase
{
    use RefreshDatabase;

    public function test_tier_validation_rejects_overlap_and_duplicates(): void
    {
        try {
            ProductPricingService::validateTiers([
                ['min_qty' => 1, 'max_qty' => 9, 'price' => 20000],
                ['min_qty' => 5, 'max_qty' => 49, 'price' => 18000],
            ]);
            $this->fail('Overlap harus ditolak.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('tumpang tindih', $e->getMessage());
        }

        try {
            ProductPricingService::validateTiers([
                ['min_qty' => 1, 'max_qty' => 9, 'price' => 20000],
                ['min_qty' => 1, 'max_qty' => 20, 'price' => 19000],
            ]);
            $this->fail('Duplikat harus ditolak.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        try {
            ProductPricingService::validateTiers([['min_qty' => 0, 'max_qty' => 9, 'price' => 20000]]);
            $this->fail('Min 0 harus ditolak.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }
    }

    public function test_price_resolution_is_deterministic(): void
    {
        $product = Product::factory()->create(['price' => 25000, 'distributor_price' => 20000]);
        $svc = app(ProductPricingService::class);
        $svc->syncChannel($product, 'customer', [
            ['min_qty' => 1, 'max_qty' => 9, 'price' => 25000],
            ['min_qty' => 10, 'max_qty' => 49, 'price' => 23000],
            ['min_qty' => 50, 'max_qty' => null, 'price' => 21000],
        ]);

        $this->assertSame(25000, $svc->resolve($product, 'customer', 1));
        $this->assertSame(23000, $svc->resolve($product, 'customer', 10));
        $this->assertSame(21000, $svc->resolve($product, 'customer', 500));
        // Distributor tanpa tier → fallback distributor_price.
        $this->assertSame(20000, $svc->resolve($product, 'distributor', 3));
    }

    public function test_spec_boundary_example_customer_vs_distributor(): void
    {
        // Contoh persis spec: Customer 1–2000 @12000, 2001+ @9000;
        // Distributor 1–50 @10000, 51+ @11500.
        $product = Product::factory()->create(['price' => 12000, 'distributor_price' => 10000]);
        $svc = app(ProductPricingService::class);
        $svc->syncChannel($product, 'customer', [
            ['min_qty' => 1, 'max_qty' => 2000, 'price' => 12000],
            ['min_qty' => 2001, 'max_qty' => null, 'price' => 9000],
        ]);
        $svc->syncChannel($product, 'distributor', [
            ['min_qty' => 1, 'max_qty' => 50, 'price' => 10000],
            ['min_qty' => 51, 'max_qty' => null, 'price' => 11500],
        ]);

        $this->assertSame(12000, $svc->resolve($product, 'customer', 1));
        $this->assertSame(12000, $svc->resolve($product, 'customer', 2000));
        $this->assertSame(9000, $svc->resolve($product, 'customer', 2001));
        $this->assertSame(10000, $svc->resolve($product, 'distributor', 1));
        $this->assertSame(10000, $svc->resolve($product, 'distributor', 50));
        $this->assertSame(11500, $svc->resolve($product, 'distributor', 51));

        // Isolasi kanal: customer tidak pernah dapat tier distributor & sebaliknya.
        $this->assertNotSame(10000, $svc->resolve($product, 'customer', 30));
        $this->assertNotSame(12000, $svc->resolve($product, 'distributor', 30));
    }

    public function test_inactive_product_cannot_be_featured(): void
    {
        $product = Product::factory()->create(['status' => 'draft']);

        try {
            app(FeaturedProductService::class)->setFeatured($product);
            $this->fail('Produk draft tidak boleh featured.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }
    }

    public function test_featured_uniqueness_preserved(): void
    {
        $a = Product::factory()->create(['status' => 'active', 'featured' => true]);
        $b = Product::factory()->create(['status' => 'active']);

        app(FeaturedProductService::class)->setFeatured($b);

        $this->assertFalse($a->refresh()->featured);
        $this->assertTrue($b->refresh()->featured);
        $this->assertSame(1, Product::where('featured', true)->count());
    }
}
