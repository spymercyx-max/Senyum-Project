<?php

namespace Tests\Feature;

use App\Services\TerritoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerritoryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalize_collapses_spaces_and_lowercases(): void
    {
        $service = app(TerritoryService::class);

        $this->assertSame('malang', $service->normalize('  MALANG  '));
        $this->assertSame('kota malang', $service->normalize('KOTA   Malang'));
    }

    public function test_find_or_create_is_idempotent(): void
    {
        $service = app(TerritoryService::class);

        $a = $service->findOrCreate('Malang', 'Klojen');
        $b = $service->findOrCreate('  malang ', ' KLOJEN ');

        $this->assertSame($a->id, $b->id);
    }
}
