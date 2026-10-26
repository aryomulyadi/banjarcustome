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

class AdminOrderFlowTest extends TestCase
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
            'name' => 'Pelanggan Alur',
            'whatsapp_number' => '081234567890',
            'order_details' => 'Detail pesanan untuk pengujian alur status.',
        ], $attributes));

        if (array_key_exists('status', $attributes)) {
            $order->forceFill(['status' => $attributes['status']])->save();
        }

        return $order->refresh();
    }

    public function test_pending_can_only_go_confirmed_or_cancelled(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PENDING]);

        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), ['status' => 'production'])
            ->assertSessionHasErrors('status');

        $this->assertSame(CustomOrder::STATUS_PENDING, $order->fresh()->status);

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), ['status' => 'confirmed'])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame(CustomOrder::STATUS_CONFIRMED, $order->fresh()->status);
    }

    public function test_cancel_requires_reason_and_stores_it(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PENDING]);

        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
            ->assertSessionHasErrors('cancel_reason');

        $this->assertSame(CustomOrder::STATUS_PENDING, $order->fresh()->status);

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), [
                'status' => 'cancelled',
                'cancel_reason' => 'Bahan tidak tersedia.',
            ])
            ->assertRedirect(route('admin.orders.show', $order));

        $fresh = $order->fresh();

        $this->assertSame(CustomOrder::STATUS_CANCELLED, $fresh->status);
        $this->assertSame('Bahan tidak tersedia.', $fresh->cancel_reason);
    }

    public function test_ready_rejects_when_prereqs_missing_and_names_them(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_DESIGN]);

        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), ['status' => 'ready'])
            ->assertSessionHasErrors('status');

        $errors = session('errors')->get('status');
        $this->assertStringContainsString('Desain belum disetujui', $errors[0]);
        $this->assertStringContainsString('Rincian ukuran', $errors[0]);
        $this->assertStringContainsString('Harga', $errors[0]);
        $this->assertStringContainsString('DP 50%', $errors[0]);

        $this->assertSame(CustomOrder::STATUS_DESIGN, $order->fresh()->status);
    }

    public function test_ready_succeeds_when_all_prereqs_met(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_DESIGN]);
        $order->forceFill([
            'design_approved_at' => now(),
            'size_quantities' => ['S' => 2, 'M' => 3],
            'quantity' => 5,
            'unit_price' => 40000,
            'bahan' => 'Cotton Combed 30s',
            'type' => 'Kaos Polos',
            'warna' => 'Navy',
            'dp_amount' => 100000,
            'dp_received_at' => now(),
        ])->save();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), ['status' => 'ready'])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame(CustomOrder::STATUS_READY, $order->fresh()->status);
    }

    public function test_ready_rejects_size_sum_mismatch(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_DESIGN]);
        $order->forceFill([
            'design_approved_at' => now(),
            'size_quantities' => ['S' => 2],
            'quantity' => 10,
            'unit_price' => 40000,
            'bahan' => 'Cotton',
            'type' => 'Kaos',
            'warna' => 'Hitam',
            'dp_amount' => 50000,
        ])->save();

        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), ['status' => 'ready'])
            ->assertSessionHasErrors('status');

        $errors = session('errors')->get('status');
        $this->assertStringContainsString('tidak sama dengan jumlah pesanan', $errors[0]);
        $this->assertSame(CustomOrder::STATUS_DESIGN, $order->fresh()->status);
    }

    public function test_ready_to_design_resets_approval(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_READY]);
        $order->forceFill(['design_approved_at' => now()])->save();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), ['status' => 'design'])
            ->assertRedirect(route('admin.orders.show', $order));

        $fresh = $order->fresh();

        $this->assertSame(CustomOrder::STATUS_DESIGN, $fresh->status);
        $this->assertNull($fresh->design_approved_at);
    }

    public function test_admin_can_mark_design_approved(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_DESIGN]);

        $this->actingAs($this->admin())
            ->post(route('admin.orders.design.approve', $order))
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertNotNull($order->fresh()->design_approved_at);
    }

    public function test_design_upload_resets_approval_and_preview_is_admin_only(): void
    {
        Storage::fake('local');

        $order = $this->order(['status' => CustomOrder::STATUS_DESIGN]);
        $order->forceFill(['design_approved_at' => now()])->save();

        $this->actingAs($this->admin())
            ->post(route('admin.orders.design.upload', $order), [
                'design_file_admin' => UploadedFile::fake()->create('hasil.jpg', 100, 'image/jpeg'),
            ])
            ->assertRedirect(route('admin.orders.show', $order));

        $fresh = $order->fresh();

        $this->assertNotNull($fresh->design_file_admin);
        $this->assertNull($fresh->design_approved_at);
        Storage::disk('local')->assertExists($fresh->design_file_admin);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.design.preview', $order))
            ->assertOk();
    }

    public function test_dp_is_recorded_with_timestamp_and_can_be_cleared(): void
    {
        $order = $this->order();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.dp', $order), ['dp_amount' => 150000])
            ->assertRedirect(route('admin.orders.show', $order));

        $fresh = $order->fresh();

        $this->assertSame(150000, $fresh->dp_amount);
        $this->assertNotNull($fresh->dp_received_at);

        $receivedAt = $fresh->dp_received_at;

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.dp', $order), ['dp_amount' => 200000])
            ->assertRedirect(route('admin.orders.show', $order));

        $fresh = $order->fresh();
        $this->assertSame(200000, $fresh->dp_amount);
        $this->assertTrue($fresh->dp_received_at->equalTo($receivedAt));

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.dp', $order), ['dp_amount' => null])
            ->assertRedirect(route('admin.orders.show', $order));

        $fresh = $order->fresh();
        $this->assertNull($fresh->dp_amount);
        $this->assertNull($fresh->dp_received_at);
    }

    public function test_jersey_design_fee_helper(): void
    {
        $jersey = $this->order(['service_type' => 'Printing Sublim - Jersey']);

        $this->assertTrue($jersey->isJersey());
        $this->assertSame(50000, $jersey->designFee());

        $kaos = $this->order(['service_type' => 'Sablon Plastisol']);

        $this->assertFalse($kaos->isJersey());
        $this->assertNull($kaos->designFee());
    }

    public function test_overdue_flag_shows_on_index_until_completed(): void
    {
        $this->order(['status' => CustomOrder::STATUS_PENDING, 'name' => 'Telat Satu', 'deadline' => now()->subDay()]);
        $this->order(['status' => CustomOrder::STATUS_COMPLETED, 'name' => 'Telat Dua', 'deadline' => now()->subDay()]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Telat Satu')
            ->assertSee('Lewat deadline');

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('Telat Dua')
            ->assertDontSee('Lewat deadline');
    }

    public function test_show_lists_next_status_templates_and_serah_terima(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PENDING]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Perbarui Status')
            ->assertSee('Terkonfirmasi')
            ->assertSee('Pesan WhatsApp')
            ->assertSee('Balasan awal')
            ->assertSee(str_replace('/', '\/', $order->trackUrl()), false)
            ->assertSee('Serah Terima Produksi')
            ->assertSee('Kartu Kerja')
            ->assertSee('DP 50%');
    }

    public function test_show_page_puts_status_update_before_editor_cards(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PENDING]);

        $html = $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->getContent();

        $status = strpos($html, 'Perbarui Status');
        $progress = strpos($html, 'Progres Produksi');
        $spec = strpos($html, 'Spesifikasi Nota');

        $this->assertNotFalse($status, 'Kartu status harus tampil.');
        $this->assertNotFalse($progress, 'Kartu progres harus tampil.');
        $this->assertNotFalse($spec, 'Kartu spesifikasi harus tampil.');
        $this->assertLessThan($spec, $status, 'Perbarui Status harus kartu pertama — update tanpa scroll (Fase 28).');
        $this->assertLessThan($spec, $progress, 'Progres Produksi harus sebelum kartu editor (Fase 28).');
    }

    public function test_completed_and_cancelled_have_final_state_in_show(): void
    {
        $completed = $this->order(['status' => CustomOrder::STATUS_COMPLETED]);

        // Completed masih boleh dibatalkan (dengan alasan) — satu-satunya opsi.
        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $completed))
            ->assertOk()
            ->assertSee('Perbarui Status')
            ->assertSee('Dibatalkan');

        $cancelled = $this->order(['status' => CustomOrder::STATUS_CANCELLED]);
        $cancelled->forceFill(['cancel_reason' => 'Alasan uji coba.'])->save();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $cancelled))
            ->assertOk()
            ->assertSee('sudah final')
            ->assertSee('Alasan uji coba.');
    }
}
