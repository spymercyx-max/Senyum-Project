<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Territory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_map_api(): void
    {
        $this->getJson('/api/developer/map/statistics')->assertUnauthorized();
    }

    public function test_distributor_is_forbidden_from_map_api(): void
    {
        $dist = User::factory()->create(['role' => 'distributor', 'status' => 'active']);
        $dist->distributorProfile()->create(['status' => 'approved']);

        $this->actingAs($dist)->getJson('/api/developer/map/statistics')->assertForbidden();
    }

    public function test_developer_gets_real_statistics_and_filters(): void
    {
        $dev = User::factory()->developer()->create();
        $t = Territory::factory()->create(['city' => 'Malang', 'district' => 'Klojen']);
        $dist = User::factory()->create(['role' => 'distributor']);
        $dist->distributorProfile()->create(['status' => 'approved', 'territory_id' => $t->id]);
        Outlet::factory()->create([
            'territory_id' => $t->id, 'distributor_id' => $dist->id,
            'status' => 'active', 'latitude' => -6.1, 'longitude' => 106.8,
        ]);
        Outlet::factory()->create([
            'territory_id' => $t->id, 'distributor_id' => $dist->id,
            'status' => 'inactive', 'latitude' => null, 'longitude' => null,
        ]);

        $res = $this->actingAs($dev)->getJson('/api/developer/map/statistics');
        $res->assertOk();
        $this->assertSame(1, $res->json('data.total_territories'));
        $this->assertSame(1, $res->json('data.total_distributors'));
        $this->assertSame(2, $res->json('data.total_outlets'));
        $this->assertSame(1, $res->json('data.active_outlets'));

        // Bounding box menyempit → hanya marker dalam viewport.
        $in = $this->actingAs($dev)->getJson('/api/developer/map/outlets?north=-6.0&south=-6.2&east=107.0&west=106.0');
        $in->assertOk();
        $this->assertSame(1, count($in->json('data')));

        $out = $this->actingAs($dev)->getJson('/api/developer/map/outlets?north=-7.0&south=-8.0&east=107.0&west=106.0');
        $this->assertSame(0, count($out->json('data')));

        // Search butuh minimal 2 karakter; tanpa password/token di respons.
        $this->actingAs($dev)->getJson('/api/developer/map/search?q=x')->assertStatus(422);
        $search = $this->actingAs($dev)->getJson('/api/developer/map/search?q=Malang');
        $search->assertOk();
        $this->assertStringNotContainsString('password', $search->getContent());
    }

    public function test_api_marker_coordinates_equal_database(): void
    {
        $dev = User::factory()->developer()->create();
        $t = Territory::factory()->create();
        $dist = User::factory()->create(['role' => 'distributor']);
        $dist->distributorProfile()->create(['status' => 'approved', 'territory_id' => $t->id]);
        $outlet = Outlet::factory()->create([
            'territory_id' => $t->id, 'distributor_id' => $dist->id,
            'status' => 'active', 'latitude' => -7.9390937, 'longitude' => 112.6247288,
        ]);

        $res = $this->actingAs($dev)->getJson('/api/developer/map/outlets');
        $res->assertOk();

        $found = collect($res->json('data'))->firstWhere('id', $outlet->id);
        $this->assertNotNull($found, 'Outlet valid harus ada di API.');
        // Presisi database (7 desimal) utuh sampai JSON.
        $this->assertEqualsWithDelta(-7.9390937, $found['lat'], 0.0000001);
        $this->assertEqualsWithDelta(112.6247288, $found['lng'], 0.0000001);
        $this->assertSame($outlet->id, $found['id']);
        $this->assertArrayNotHasKey('password', $found);
    }
}
