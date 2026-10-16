<?php

namespace Tests\Feature;

use App\Models\CustomOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithXlsx;
use Tests\TestCase;

class AdminOrderPriceTest extends TestCase
{
    use InteractsWithXlsx;
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function order(array $attributes = []): CustomOrder
    {
        return CustomOrder::create(array_merge([
            'name' => 'Pelanggan Harga',
            'whatsapp_number' => '081234567890',
            'quantity' => 24,
        ], $attributes));
    }

    public function test_unit_price_saves_and_computes_total_automatically(): void
    {
        $order = $this->order();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.orders.price', $order), ['unit_price' => 45000])
            ->assertRedirect(route('admin.orders.show', $order));

        $order->refresh();

        $this->assertSame(45000, $order->unit_price);
        $this->assertSame(1080000, $order->total_price);
    }

    public function test_explicit_total_overrides_automatic_calculation_and_can_be_cleared(): void
    {
        $order = $this->order();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.orders.price', $order), [
                'unit_price' => 45000,
                'total_price' => 999999,
            ]);

        $order->refresh();
        $this->assertSame(999999, $order->total_price);

        $this->actingAs($admin)
            ->patch(route('admin.orders.price', $order), [
                'unit_price' => '',
                'total_price' => '',
            ]);

        $order->refresh();
        $this->assertNull($order->unit_price);
        $this->assertNull($order->total_price);
    }

    public function test_negative_price_is_rejected(): void
    {
        $order = $this->order(['unit_price' => 45000]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.price', $order), ['unit_price' => -1])
            ->assertSessionHasErrors('unit_price');

        $this->assertSame(45000, $order->fresh()->unit_price);
    }

    public function test_detail_and_print_show_price_rows(): void
    {
        $order = $this->order(['unit_price' => 45000]);
        $order->forceFill(['total_price' => 1080000])->save();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Harga satuan')
            ->assertSee('Rp 45.000')
            ->assertSee('Rp 1.080.000');

        $this->actingAs($admin)
            ->get(route('admin.orders.print', $order))
            ->assertOk()
            ->assertSee('Rincian Perhitungan')
            ->assertSee('Total Harga')
            ->assertSee('Rp 1.080.000');
    }

    public function test_print_shows_express_fee_when_price_set(): void
    {
        config()->set('banjarcustom.express_fee', 'Rp 50.000');

        $order = $this->order([
            'unit_price' => 45000,
            'total_price' => 1080000,
            'is_express' => true,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.print', $order))
            ->assertOk()
            ->assertSee('Biaya express')
            ->assertSee('Rp 50.000');
    }

    public function test_whatsapp_summary_contains_express_fee(): void
    {
        config()->set('banjarcustom.express_fee', 'Rp 50.000');

        $order = $this->order(['is_express' => true]);

        $link = $order->whatsappLink();

        $this->assertStringContainsString(rawurlencode('biaya tambahan Rp 50.000'), $link);

        config()->set('banjarcustom.express_fee', null);

        $this->assertStringNotContainsString(rawurlencode('biaya tambahan'), $order->whatsappLink());
    }

    public function test_success_page_shows_express_fee(): void
    {
        config()->set('banjarcustom.express_fee', 'Rp 50.000');

        $order = $this->order(['is_express' => true]);

        $this->get(route('pesan.success', ['token' => $order->tracking_token]))
            ->assertOk()
            ->assertSee('biaya Rp 50.000');
    }

    public function test_xlsx_export_includes_total_price_and_progress_flags(): void
    {
        $order = $this->order([
            'unit_price' => 45000,
            'total_price' => 1080000,
            'is_express' => true,
            'source' => CustomOrder::SOURCE_IMPORT,
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.orders.export'));

        $text = $this->xlsxText($response);

        $this->assertStringContainsString('TOTAL', $text);
        $this->assertStringContainsString('1080000', $text);
        $this->assertStringContainsString('Pelanggan Harga', $text);
        $this->assertStringContainsString('nok', $text);
        $this->assertNotFalse($order->id);
    }

    public function test_price_mismatch_with_quantity_sends_warning(): void
    {
        $order = $this->order();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.price', $order), [
                'unit_price' => 45000,
                'total_price' => 999999,
            ])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('status');

        $this->assertStringContainsString(
            'tidak sama dengan jumlah rincian',
            (string) session('status')
        );

        // Nilai tetap tersimpan (peringatan, bukan penolakan).
        $this->assertSame(999999, $order->fresh()->total_price);

        $this->actingAs($admin)
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.price', $order), [
                'unit_price' => 45000,
                'total_price' => 1080000,
            ]);

        $this->assertStringNotContainsString(
            'tidak sama dengan satuan',
            (string) session('status')
        );
    }

    public function test_print_and_show_grand_total_includes_express_fee(): void
    {
        config()->set('banjarcustom.express_fee', '50000');

        $order = $this->order([
            'unit_price' => 45000,
            'total_price' => 1080000,
            'is_express' => true,
        ]);

        $this->assertSame(1130000, $order->grandTotalPrice());

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Subtotal')
            ->assertSee('Biaya express')
            ->assertSee('Rp 1.130.000');

        $this->actingAs($this->admin())
            ->get(route('admin.orders.print', $order))
            ->assertOk()
            ->assertSee('Subtotal')
            ->assertSee('Total pembayaran')
            ->assertSee('Rp 1.130.000');

        config()->set('banjarcustom.express_fee', null);
    }

    public function test_xlsx_export_keeps_formula_like_text_as_plain_string(): void
    {
        $this->order([
            'name' => '=HYPERLINK("http://evil","klik")',
            'whatsapp_number' => '081234567890',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.orders.export'));

        $rows = $this->xlsxRows($response);

        $header = $rows[0];
        $nameIndex = array_search('customer', $header, true);

        $this->assertNotFalse($nameIndex);

        // Nilai tersimpan sebagai sel teks apa adanya di XLSX — bukan formula
        // yang dieksekusi Excel (tidak ada tipe cell formula).
        $this->assertSame('=HYPERLINK("http://evil","klik")', $rows[1][$nameIndex]);
    }
}
