<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkCardTest extends TestCase
{
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
            'name' => 'Pelanggan SPK',
            'whatsapp_number' => '081377778888',
            'order_details' => 'Detail pesanan untuk pengujian kartu kerja dan SPK.',
            'quantity' => 5,
            'size_quantities' => ['S' => 2, 'M' => 3],
            'unit_price' => 45000,
            'bahan' => 'Cotton Combed 30s',
            'type' => 'Kaos Polos',
            'warna' => 'Navy',
            'deadline' => now()->addWeek(),
        ], $attributes));

        if (array_key_exists('status', $attributes)) {
            $order->forceFill(['status' => $attributes['status']])->save();
        }

        return $order->refresh();
    }

    public function test_card_print_requires_admin_and_shows_rows(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_READY]);

        $this->get(route('admin.orders.card', $order))->assertRedirect(route('login'));

        $this->actingAs($this->admin())
            ->get(route('admin.orders.card', $order))
            ->assertOk()
            ->assertSee('KARTU KERJA PRODUKSI')
            ->assertSee('Pelanggan SPK')
            ->assertSee('Cotton Combed 30s')
            ->assertSee('2 pcs')
            ->assertSee('Rp 45.000');
    }

    public function test_spk_generates_number_once_and_shows_banner(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_READY]);
        $order->forceFill(['assigned_operator' => 'Pak Rusdi'])->save();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.spk', $order))
            ->assertOk()
            ->assertSee('SPK Produksi')
            ->assertSee('BANJAR CUSTOME')
            ->assertSee('Deadline Produksi')
            ->assertSee('Rincian Ukuran')
            ->assertSee('Total Keseluruhan')
            ->assertSee('Pelanggan SPK')
            ->assertSee('Cotton Combed 30s')
            ->assertDontSee('SPK-0001')
            ->assertDontSee('Pak Rusdi')
            ->assertDontSee('Rp 45.000')
            ->assertDontSee('https://wa.me');

        $fresh = $order->fresh();

        $this->assertSame('SPK-0001', $fresh->spk_number);
        $this->assertNotNull($fresh->spk_issued_at);

        $issuedAt = $fresh->spk_issued_at;

        $this->actingAs($this->admin())
            ->get(route('admin.orders.spk', $order))
            ->assertOk();

        $this->assertTrue($order->fresh()->spk_issued_at->equalTo($issuedAt));
    }

    public function test_spk_shows_collar_for_jersey_only(): void
    {
        $jersey = $this->order([
            'status' => CustomOrder::STATUS_READY,
            'service_type' => 'Printing Sublim — Jersey',
            'collar_model' => 'Wangky Zipper',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.spk', $jersey))
            ->assertOk()
            ->assertSee('Model Kerah')
            ->assertSee('Wangky Zipper');

        $kaos = $this->order(['status' => CustomOrder::STATUS_READY]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.spk', $kaos))
            ->assertOk()
            ->assertDontSee('Model Kerah');
    }

    public function test_spec_update_saves_collar_model(): void
    {
        $order = $this->order();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.spec', $order), [
                'bahan' => 'Dryfit Milano',
                'type' => 'Jersey Futsal',
                'warna' => 'Merah',
                'collar_model' => 'V-Neck Tumpuk',
            ])
            ->assertRedirect(route('admin.orders.show', $order));

        $fresh = $order->fresh();

        $this->assertSame('V-Neck Tumpuk', $fresh->collar_model);

        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.spec', $order), ['collar_model' => str_repeat('X', 61)])
            ->assertSessionHasErrors('collar_model');

        $this->assertSame('V-Neck Tumpuk', $order->fresh()->collar_model);
    }

    public function test_handover_saves_operator(): void
    {
        $order = $this->order();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.handover', $order), ['assigned_operator' => 'Operator Sublim A'])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame('Operator Sublim A', $order->fresh()->assigned_operator);

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.handover', $order), ['assigned_operator' => null])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertNull($order->fresh()->assigned_operator);
    }

    public function test_design_preview_streams_for_admin_only(): void
    {
        Storage::fake('local');

        $order = $this->order();
        $path = UploadedFile::fake()->create('preview.jpg', 50, 'image/jpeg')
            ->storeAs('designs/admin', 'preview.jpg', 'local');
        $order->forceFill(['design_file_admin' => $path])->save();

        $this->get(route('admin.orders.design.preview', $order))->assertRedirect(route('login'));

        $this->actingAs($this->admin())
            ->get(route('admin.orders.design.preview', $order))
            ->assertOk();
    }
}
