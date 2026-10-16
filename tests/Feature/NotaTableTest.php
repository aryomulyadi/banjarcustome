<?php

namespace Tests\Feature;

use App\Models\CustomOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotaTableTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function order(array $attributes = []): CustomOrder
    {
        return CustomOrder::create(array_merge([
            'name' => 'Pelanggan Nota',
            'whatsapp_number' => '081234567890',
            'quantity' => 24,
            'unit_price' => 45000,
            'size_quantities' => ['S' => 6, 'M' => 12, 'L' => 6],
            'service_type' => 'Sablon Plastisol',
        ], $attributes));
    }

    public function test_print_shows_calculation_table_with_customer_name(): void
    {
        $order = $this->order([
            'bahan' => 'Cotton Combed 30s',
            'type' => 'Kaos Oblong',
            'warna' => 'Navy',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.orders.print', $order));

        $response->assertOk()
            ->assertSee('Nama Customer')
            ->assertSee('Pelanggan Nota')
            ->assertSee('Rincian Perhitungan')
            ->assertSee('Jenis')
            ->assertSee('Bahan')
            ->assertSee('Type')
            ->assertSee('Warna')
            ->assertSee('Ukuran')
            ->assertSee('Jumlah')
            ->assertSee('Harga')
            ->assertSee('Total Harga')
            ->assertSee('Sablon Plastisol')
            ->assertSee('Cotton Combed 30s')
            ->assertSee('Kaos Oblong')
            ->assertSee('Navy')
            ->assertSee('Rp 45.000')
            ->assertSee('Rp 1.080.000')
            ->assertSee('Subtotal')
            ->assertSee('Total pembayaran');

        // Satu baris per ukuran (bukan satu cell gabungan "S: 6, M: 12, ...").
        $content = $response->getContent();
        $this->assertSame(3, substr_count($content, '<td class="wrap">'));
        $this->assertStringContainsString('<td class="wrap">S</td>', $content);
        $this->assertStringContainsString('<td class="wrap">M</td>', $content);
        $this->assertStringContainsString('<td class="wrap">L</td>', $content);
        $this->assertStringContainsString('12 pcs', $content);
        $this->assertStringNotContainsString('S: 6, M: 12, L: 6', $content);
    }

    public function test_print_omits_deadline_estimation_delivery_and_address(): void
    {
        $order = $this->order([
            'deadline' => '2026-10-23',
            'is_express' => false,
            'delivery_method' => 'kirim',
            'address' => 'Jl. Contoh No. 1, Banjarmasin',
        ]);

        $content = $this->actingAs($this->admin())
            ->get(route('admin.orders.print', $order))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('>Deadline<', $content);
        $this->assertStringNotContainsString('>Estimasi<', $content);
        $this->assertStringNotContainsString('>Pengiriman<', $content);
        $this->assertStringNotContainsString('>Alamat<', $content);
        $this->assertStringNotContainsString('23 Oktober 2026', $content);
        $this->assertStringNotContainsString('kaos 3-7 hari kerja', $content);
        $this->assertStringNotContainsString('Jl. Contoh No. 1', $content);
    }

    public function test_print_omits_order_number_and_status(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PRODUCTION]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.orders.print', $order));

        $response->assertOk();

        $content = $response->getContent();

        // Nomor nota & status pesanan tidak tampil di lembar cetak.
        $this->assertStringNotContainsString('<p class="muted">#', $content);
        $this->assertStringNotContainsString('Nota Pesanan #', $content);
        $this->assertStringNotContainsString('Sedang Diproses', $content);
        $this->assertStringNotContainsString('class="badge"', $content);
        $this->assertStringContainsString('<title>Nota Pesanan — Banjar Custome</title>', $content);
    }

    public function test_print_shows_surcharge_per_size_row(): void
    {
        $order = $this->order([
            'quantity' => 14,
            'size_quantities' => ['S' => 10, 'XXL' => 4],
        ]);

        $content = $this->actingAs($this->admin())
            ->get(route('admin.orders.print', $order))
            ->assertOk()
            ->getContent();

        // S tetap 45.000; XXL = 45.000 + 10.000 surcharge.
        $this->assertStringContainsString('<td class="wrap">XXL</td>', $content);
        $this->assertStringContainsString('Rp 55.000', $content);
        // Subtotal = (10 × 45.000) + (4 × 55.000) = 670.000 (bukan 14 × 45.000).
        $this->assertStringContainsString('Rp 670.000', $content);
        $this->assertStringNotContainsString('Rp 630.000', $content);
    }

    public function test_print_uses_saved_size_price_override(): void
    {
        $order = $this->order([
            'quantity' => 14,
            'size_quantities' => ['S' => 10, 'XXL' => 4],
            'size_prices' => ['XXL' => 60000],
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.print', $order))
            ->assertOk()
            ->assertSee('Rp 60.000')
            ->assertSee('Rp 690.000'); // (10 × 45.000) + (4 × 60.000)
    }

    public function test_print_shows_manual_adjustment_when_total_differs(): void
    {
        $order = $this->order([
            'quantity' => 14,
            'size_quantities' => ['S' => 10, 'XXL' => 4],
            'total_price' => 700000,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.print', $order))
            ->assertOk()
            ->assertSee('Subtotal')
            ->assertSee('Rp 670.000')          // Σ rincian
            ->assertSee('Penyesuaian manual')
            ->assertSee('+ Rp 30.000')          // 700.000 − 670.000
            ->assertSee('Rp 700.000');          // total pembayaran
    }

    public function test_print_without_unit_price_shows_placeholder_rows(): void
    {
        $order = $this->order(['unit_price' => null]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.print', $order))
            ->assertOk()
            ->assertDontSee('Subtotal')
            ->assertDontSee('Total pembayaran')
            ->assertSee('Harga & pembayaran menyusul konfirmasi CS.');
    }

    public function test_admin_can_save_specification_for_print(): void
    {
        $order = $this->order();

        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.spec', $order), [
                'bahan' => 'Dryfit Super',
                'type' => 'Jersey Printing',
                'warna' => 'Merah Marun',
            ])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('status');

        $order->refresh();

        $this->assertSame('Dryfit Super', $order->bahan);
        $this->assertSame('Jersey Printing', $order->type);
        $this->assertSame('Merah Marun', $order->warna);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Spesifikasi Nota')
            ->assertSee('Dryfit Super');

        $this->actingAs($this->admin())
            ->get(route('admin.orders.print', $order))
            ->assertOk()
            ->assertSee('Dryfit Super')
            ->assertSee('Jersey Printing')
            ->assertSee('Merah Marun');
    }

    public function test_specification_validation_limits_length(): void
    {
        $order = $this->order();

        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.spec', $order), [
                'bahan' => str_repeat('a', 121),
            ])
            ->assertSessionHasErrors('bahan');
    }

    public function test_calculator_page_has_quote_inputs_and_table(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.calculator'));

        $response->assertOk()
            ->assertSee('Nama Customer')
            ->assertSee('Tabel Perhitungan')
            ->assertSee('Cetak Penawaran')
            ->assertSee('Bahan')
            ->assertSee('Warna')
            ->assertSee('Ukuran')
            ->assertSee('Total pembayaran');
    }

    public function test_calculator_print_renders_quote_table_from_query(): void
    {
        config()->set('banjarcustom.express_fee', '50000');

        $response = $this->actingAs($this->admin())->get(route('admin.calculator.print', [
            'customer' => 'Budi Santoso',
            'jenis' => 'Sablon Plastisol',
            'bahan' => 'Cotton Combed 30s',
            'type' => 'Kaos Oblong',
            'warna' => 'Navy',
            'ukuran' => 'S:10, M:14',
            'qty' => 24,
            'unit' => 45000,
            'express' => '1',
        ]));

        $response->assertOk()
            ->assertSee('PENAWARAN HARGA')
            ->assertSee('Nama Customer')
            ->assertSee('Budi Santoso')
            ->assertSee('Cotton Combed 30s')
            ->assertSee('S:10, M:14')
            ->assertSee('24 pcs')
            ->assertSee('Rp 45.000')
            ->assertSee('Rp 1.080.000')
            ->assertSee('Biaya express')
            ->assertSee('Rp 50.000')
            ->assertSee('Rp 1.130.000');

        config()->set('banjarcustom.express_fee', null);
    }

    public function test_calculator_print_requires_admin(): void
    {
        $this->get(route('admin.calculator.print', ['customer' => 'X']))
            ->assertRedirect(route('login'));

        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.calculator.print', ['customer' => 'X']))
            ->assertForbidden();
    }

    public function test_calculator_print_bounds_inputs(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.calculator.print', [
            'customer' => str_repeat('x', 500),
            'qty' => '-99',
            'unit' => '-1',
        ]));

        $response->assertOk();

        // Qty minimal 1, unit minimal 0, nama dipotong maks 120 karakter.
        $this->assertStringContainsString('1 pcs', $response->getContent());
        $this->assertStringContainsString('Rp 0', $response->getContent());
        $this->assertStringNotContainsString(str_repeat('x', 121), $response->getContent());
    }

    public function test_calculator_print_renders_one_row_per_size_from_sizes_param(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.calculator.print', [
            'customer' => 'Budi Santoso',
            'jenis' => 'Sablon Plastisol',
            'bahan' => 'Cotton Combed 30s',
            'type' => 'Kaos Oblong',
            'warna' => 'Navy',
            'ukuran' => 'S:10, XXL:14',
            'qty' => 999, // diabaikan — dihitung ulang dari sizes
            'unit' => 45000,
            'sizes' => json_encode([
                ['s' => 'S', 'q' => 10, 'u' => 45000],
                ['s' => 'XXL', 'q' => 14, 'u' => 55000],
            ]),
        ]));

        $content = $response->assertOk()->getContent();

        $this->assertSame(2, substr_count($content, '<td class="wrap">'));
        $this->assertStringContainsString('<td class="wrap">S</td>', $content);
        $this->assertStringContainsString('<td class="wrap">XXL</td>', $content);
        $this->assertStringContainsString('10 pcs', $content);
        $this->assertStringContainsString('14 pcs', $content);
        $this->assertStringContainsString('Rp 55.000', $content);
        // Subtotal = (10 × 45.000) + (14 × 55.000) = 1.220.000.
        $this->assertStringContainsString('Rp 1.220.000', $content);
        // Kolom identik hanya baris pertama.
        $this->assertSame(1, substr_count($content, 'Cotton Combed 30s'));
    }

    public function test_calculator_print_rejects_invalid_sizes_and_falls_back(): void
    {
        // Item tak valid (ukuran aneh, qty negatif, harga kelewatan) dibuang;
        // bila tak ada baris valid → fallback ke qty/unit biasa.
        $response = $this->actingAs($this->admin())->get(route('admin.calculator.print', [
            'customer' => 'Fallback',
            'ukuran' => 'S:24',
            'qty' => 24,
            'unit' => 45000,
            'sizes' => json_encode([
                ['s' => '../etc', 'q' => 10, 'u' => 45000],
                ['s' => 'S', 'q' => -5, 'u' => 45000],
                ['s' => 'M', 'q' => 10, 'u' => 999999999999],
            ]),
        ]));

        $content = $response->assertOk()->getContent();

        $this->assertStringContainsString('24 pcs', $content);
        $this->assertStringContainsString('Rp 1.080.000', $content); // 24 × 45.000
    }

    public function test_calculator_print_oversized_sizes_query_is_ignored(): void
    {
        $response = $this->actingAs($this->admin())->get(route('admin.calculator.print', [
            'qty' => 2,
            'unit' => 10000,
            'sizes' => str_repeat('a', 5000),
        ]));

        $content = $response->assertOk()->getContent();

        $this->assertStringContainsString('2 pcs', $content);
        $this->assertStringContainsString('Rp 20.000', $content);
    }
}
