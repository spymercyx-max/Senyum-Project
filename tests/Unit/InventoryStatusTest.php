<?php

namespace Tests\Unit;

use App\Models\Inventory;
use App\Services\InventoryService;
use PHPUnit\Framework\TestCase;

class InventoryStatusTest extends TestCase
{
    protected function checkStatus(int $stock, int $reserved, int $threshold): string
    {
        $inv = new Inventory(['stock' => $stock, 'reserved' => $reserved, 'threshold' => $threshold]);

        return (new InventoryService())->stockStatus($inv);
    }

    public function test_out_when_nothing_available(): void
    {
        $this->assertSame('out', $this->checkStatus(0, 0, 10));
        $this->assertSame('out', $this->checkStatus(5, 5, 10));
    }

    public function test_critical_low_healthy(): void
    {
        // threshold 10: critical <= 5, low <= 10, healthy > 10
        $this->assertSame('critical', $this->checkStatus(3, 0, 10));
        $this->assertSame('low', $this->checkStatus(8, 0, 10));
        $this->assertSame('healthy', $this->checkStatus(50, 0, 10));
    }

    public function test_zero_threshold_means_healthy_when_available(): void
    {
        $this->assertSame('healthy', $this->checkStatus(1, 0, 0));
    }
}
