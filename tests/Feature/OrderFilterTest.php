<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithXlsx;
use Tests\TestCase;

class OrderFilterTest extends TestCase
{
    use InteractsWithXlsx;
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function order(array $attributes = []): CustomOrder
    {
        $category = Category::firstOrCreate(['slug' => 'sablon'], ['name' => 'Sablon']);
        $product = Product::firstOrCreate(['slug' => 'kaos-sablon'], [
            'category_id' => $category->id,
            'title' => 'Kaos Sablon',
        ]);

        $order = CustomOrder::create(array_merge([
            'product_id' => $product->id,
            'name' => 'Pelanggan Filter',
            'whatsapp_number' => '081111111111',
            'order_details' => 'Detail pesanan untuk pengujian filter bulan dan jenis.',
        ], $attributes));

        if (array_key_exists('status', $attributes)) {
            $order->forceFill(['status' => $attributes['status']])->save();
        }

        if (array_key_exists('created_at', $attributes)) {
            $order->forceFill(['created_at' => $attributes['created_at']])->save();
        }

        return $order->refresh();
    }

    public function test_index_filters_by_month(): void
    {
        $this->order(['name' => 'Pesanan Oktober', 'created_at' => '2026-10-15 10:00:00']);
        $this->order(['name' => 'Pesanan November', 'created_at' => '2026-11-05 10:00:00']);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['bulan' => '2026-10']))
            ->assertOk()
            ->assertSee('Pesanan Oktober')
            ->assertDontSee('Pesanan November');

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['bulan' => 'bukan-bulan']))
            ->assertOk()
            ->assertSee('Pesanan Oktober')
            ->assertSee('Pesanan November');
    }

    public function test_index_filters_by_jenis(): void
    {
        $this->order(['name' => 'Pesanan Jersey', 'service_type' => 'Jersey']);
        $this->order(['name' => 'Pesanan Kaos', 'service_type' => 'Kaos Sablon']);
        $this->order(['name' => 'Pesanan Polos', 'service_type' => null]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['jenis' => ['Jersey']]))
            ->assertOk()
            ->assertSee('Pesanan Jersey')
            ->assertDontSee('Pesanan Kaos')
            ->assertDontSee('Pesanan Polos');

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['jenis' => ['kosong']]))
            ->assertOk()
            ->assertSee('Pesanan Polos')
            ->assertDontSee('Pesanan Jersey');

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['jenis' => ['Jersey', 'kosong']]))
            ->assertOk()
            ->assertSee('Pesanan Jersey')
            ->assertSee('Pesanan Polos')
            ->assertDontSee('Pesanan Kaos');
    }

    public function test_index_month_and_jenis_filters_combine_with_status(): void
    {
        $this->order([
            'name' => 'Target Combo',
            'service_type' => 'Jersey',
            'status' => CustomOrder::STATUS_COMPLETED,
            'created_at' => '2026-10-15 10:00:00',
        ]);
        $this->order([
            'name' => 'Salah Bulan',
            'service_type' => 'Jersey',
            'status' => CustomOrder::STATUS_COMPLETED,
            'created_at' => '2026-11-15 10:00:00',
        ]);
        $this->order([
            'name' => 'Salah Status',
            'service_type' => 'Jersey',
            'status' => CustomOrder::STATUS_PENDING,
            'created_at' => '2026-10-20 10:00:00',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', [
                'bulan' => '2026-10',
                'jenis' => ['Jersey'],
                'status' => CustomOrder::STATUS_COMPLETED,
            ]))
            ->assertOk()
            ->assertSee('Target Combo')
            ->assertDontSee('Salah Bulan')
            ->assertDontSee('Salah Status');
    }

    public function test_export_respects_month_and_jenis_filters(): void
    {
        $this->order(['name' => 'Ekspor Oktober', 'created_at' => '2026-10-15 10:00:00', 'service_type' => 'Jersey']);
        $this->order(['name' => 'Ekspor November', 'created_at' => '2026-11-05 10:00:00', 'service_type' => 'Jersey']);
        $this->order(['name' => 'Ekspor Kaos', 'created_at' => '2026-10-20 10:00:00', 'service_type' => 'Kaos Sablon']);

        $export = $this->actingAs($this->admin())->get(route('admin.orders.export', [
            'bulan' => '2026-10',
            'jenis' => ['Jersey'],
        ]));

        $text = $this->xlsxText($export);

        $this->assertStringContainsString('Ekspor Oktober', $text);
        $this->assertStringNotContainsString('Ekspor November', $text);
        $this->assertStringNotContainsString('Ekspor Kaos', $text);
    }

    public function test_index_shows_bulan_and_jenis_filter_controls(): void
    {
        $this->order(['created_at' => '2026-10-15 10:00:00', 'service_type' => 'Jersey']);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Semua bulan')
            ->assertSee('Oktober 2026')
            ->assertSee('Jersey')
            ->assertSee('Tanpa jenis');
    }
}
