<?php

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Territory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletMapsTest extends TestCase
{
    use RefreshDatabase;

    private function distributor(): User
    {
        $dist = User::factory()->create(['role' => 'distributor', 'status' => 'active']);
        $territory = Territory::factory()->create(['city' => 'Malang', 'district' => 'Klojen']);
        $dist->distributorProfile()->create(['status' => 'approved', 'territory_id' => $territory->id]);
        return $dist;
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'name' => 'Toko Maju',
            'address' => 'Jl. Merdeka 1',
            'city' => 'Malang',
            'district' => 'Klojen',
            'phone' => '628111',
            'google_maps_url' => 'https://www.google.com/maps/@-6.1754,106.8272,17z',
            'notes' => null,
        ], $over);
    }

    public function test_outlet_creation_stores_precise_coordinates(): void
    {
        $dist = $this->distributor();

        $response = $this->actingAs($dist)->post(route('distributor.outlet.store'), $this->payload());
        $response->assertRedirect();

        $outlet = Outlet::where('name', 'Toko Maju')->firstOrFail();
        $this->assertEqualsWithDelta(-6.1754, (float) $outlet->latitude, 0.0001);
        $this->assertEqualsWithDelta(106.8272, (float) $outlet->longitude, 0.0001);
        $this->assertSame('google_maps', $outlet->location_source);
        $this->assertNotNull($outlet->location_verified_at);
        $this->assertDatabaseHas('activity_logs', ['action' => 'outlet.created']);
    }

    public function test_unreadable_link_rejected_without_guessing(): void
    {
        $dist = $this->distributor();

        $response = $this->actingAs($dist)
            ->from(route('distributor.outlet.create'))
            ->post(route('distributor.outlet.store'), $this->payload(['google_maps_url' => 'https://example.com/bukan']));

        $response->assertSessionHasErrors('google_maps_url');
        $this->assertSame(0, Outlet::count());
    }

    public function test_edit_with_new_link_updates_coordinates_and_logs(): void
    {
        $dist = $this->distributor();
        $this->actingAs($dist)->post(route('distributor.outlet.store'), $this->payload());
        $outlet = Outlet::firstOrFail();

        $this->actingAs($dist)->put(route('distributor.outlet.update', $outlet), $this->payload([
            'google_maps_url' => 'https://maps.google.com/?q=-7.2575,112.7521',
        ]))->assertRedirect();

        $outlet->refresh();
        $this->assertEqualsWithDelta(-7.2575, (float) $outlet->latitude, 0.0001);
        $this->assertDatabaseHas('activity_logs', ['action' => 'outlet.location_changed']);
    }

    public function test_outlet_of_other_distributor_is_forbidden(): void
    {
        $a = $this->distributor();
        $b = $this->distributor();
        $this->actingAs($a)->post(route('distributor.outlet.store'), $this->payload());
        $outlet = Outlet::firstOrFail();

        $this->actingAs($b)->get(route('distributor.outlet.show', $outlet))->assertForbidden();
    }
}
