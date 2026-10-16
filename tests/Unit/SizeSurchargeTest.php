<?php

namespace Tests\Unit;

use App\Support\SizeSurcharge;
use Tests\TestCase;

class SizeSurchargeTest extends TestCase
{
    public function test_small_sizes_have_no_surcharge(): void
    {
        $this->assertSame(0, SizeSurcharge::of('S'));
        $this->assertSame(0, SizeSurcharge::of('M'));
        $this->assertSame(0, SizeSurcharge::of('L'));
        $this->assertSame(0, SizeSurcharge::of('XL'));
        $this->assertSame(0, SizeSurcharge::of(''));
        $this->assertSame(0, SizeSurcharge::of('anak'));
    }

    public function test_xxl_and_up_have_step_surcharge(): void
    {
        $this->assertSame(10000, SizeSurcharge::of('XXL'));
        $this->assertSame(20000, SizeSurcharge::of('3XL'));
        $this->assertSame(20000, SizeSurcharge::of('XXXL'));
        $this->assertSame(30000, SizeSurcharge::of('4XL'));
        $this->assertSame(40000, SizeSurcharge::of('5XL'));
        $this->assertSame(50000, SizeSurcharge::of('6XL'));
        $this->assertSame(90000, SizeSurcharge::of('10XL'));
    }

    public function test_size_is_normalized_case_and_whitespace(): void
    {
        $this->assertSame(10000, SizeSurcharge::of('xxl'));
        $this->assertSame(30000, SizeSurcharge::of(' 4xl '));
    }

    public function test_config_override_wins_over_pattern(): void
    {
        config(['banjarcustom.size_surcharges.XXL' => 12345]);
        $this->assertSame(12345, SizeSurcharge::of('XXL'));

        config(['banjarcustom.size_surcharges.YY' => 7777]);
        $this->assertSame(7777, SizeSurcharge::of('yy'));
    }

    public function test_unknown_two_letter_size_is_zero(): void
    {
        $this->assertSame(0, SizeSurcharge::of('2XL'));
        $this->assertSame(0, SizeSurcharge::of('XLL'));
        $this->assertSame(0, SizeSurcharge::of('ABC'));
    }
}
