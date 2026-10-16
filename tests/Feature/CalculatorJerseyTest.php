<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorJerseyTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_calculator_shows_jersey_panel_with_rules(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.calculator'));

        $response->assertOk()
            ->assertSee('Panel Jersey 2026-2027')
            ->assertSee('VIP ORDER')
            ->assertSee('Biaya desain')
            ->assertSee('Wangky Zipper')
            ->assertSee('Shanghai Zipper')
            ->assertSee('Dryfit Milano')
            ->assertSee('Jaqcuard Camo')
            ->assertSee(route('panduan-jersey'), false);

        $content = $response->getContent();
        $this->assertStringContainsString('base_price&quot;:85000', $content);
        $this->assertStringContainsString('min_order&quot;:12', $content);
        $this->assertStringContainsString('vip_fee&quot;:10000', $content);
    }

    public function test_print_with_design_fee_includes_jersey_row(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.calculator.print', [
            'customer' => 'Pel Jersey',
            'jenis' => 'Printing Sublim — Jersey',
            'qty' => 2,
            'unit' => 100000,
            'desain' => '1',
        ]));

        $response->assertOk()
            ->assertSee('Biaya desain jersey (revisi bebas)')
            ->assertSee('Rp 50.000')
            ->assertSee('Rp 250.000');
    }

    public function test_print_without_design_fee_omits_row(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.calculator.print', [
            'customer' => 'Pel Biasa',
            'jenis' => 'Sablon DTF',
            'qty' => 2,
            'unit' => 100000,
        ]));

        $response->assertOk()
            ->assertDontSee('Biaya desain jersey')
            ->assertSee('Rp 200.000');
    }
}
