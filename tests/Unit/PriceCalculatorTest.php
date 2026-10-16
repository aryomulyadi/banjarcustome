<?php

namespace Tests\Unit;

use App\Support\PriceCalculator;
use PHPUnit\Framework\TestCase;

class PriceCalculatorTest extends TestCase
{
    public function test_returns_null_without_base_price_and_tiers(): void
    {
        $this->assertNull(PriceCalculator::unitPrice(null, null, 10));
        $this->assertNull(PriceCalculator::unitPrice(null, [], 10));
    }

    public function test_falls_back_to_base_price_when_no_tier_matches(): void
    {
        $this->assertSame(45000, PriceCalculator::unitPrice(45000, [], 10));
        $this->assertSame(45000, PriceCalculator::unitPrice(45000, [
            ['min_qty' => 50, 'unit_price' => 40000],
        ], 49));
    }

    public function test_picks_highest_matching_tier(): void
    {
        $tiers = [
            ['min_qty' => 10, 'unit_price' => 42000],
            ['min_qty' => 50, 'unit_price' => 40000],
            ['min_qty' => 100, 'unit_price' => 38000],
        ];

        $this->assertSame(42000, PriceCalculator::unitPrice(45000, $tiers, 10));
        $this->assertSame(42000, PriceCalculator::unitPrice(45000, $tiers, 49));
        $this->assertSame(40000, PriceCalculator::unitPrice(45000, $tiers, 50));
        $this->assertSame(38000, PriceCalculator::unitPrice(45000, $tiers, 150));
    }

    public function test_works_with_unsorted_and_string_tiers(): void
    {
        $tiers = [
            ['min_qty' => '100', 'unit_price' => '38000'],
            ['min_qty' => '50', 'unit_price' => '40000'],
        ];

        $this->assertSame(40000, PriceCalculator::unitPrice(null, $tiers, 60));
        $this->assertSame(38000, PriceCalculator::unitPrice(null, $tiers, 600));
        $this->assertNull(PriceCalculator::unitPrice(null, $tiers, 10));
    }

    public function test_total_includes_optional_extra_fee(): void
    {
        $this->assertSame(450000, PriceCalculator::total(45000, 10));
        $this->assertSame(500000, PriceCalculator::total(45000, 10, 50000));
    }
}
