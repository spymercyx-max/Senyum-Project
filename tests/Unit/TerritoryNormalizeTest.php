<?php

namespace Tests\Unit;

use App\Services\TerritoryService;
use PHPUnit\Framework\TestCase;

class TerritoryNormalizeTest extends TestCase
{
    public function test_normalize(): void
    {
        $service = new TerritoryService();

        $this->assertSame('malang', $service->normalize('  MALANG '));
        $this->assertSame('klojen', $service->normalize('KLOJEN'));
        $this->assertSame('kota malang', $service->normalize("KOTA\t  Malang\n"));
        $this->assertSame('sidoarjo', TerritoryService::normalizeCity(' SIDOARJO '));
        $this->assertSame('taman', TerritoryService::normalizeDistrict('TAMAN'));
    }
}
