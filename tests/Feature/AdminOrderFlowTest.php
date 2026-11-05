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

    public function test_pending_can_jump_free_but_unknown_status_rejected(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PENDING]);

        // Tahap asing (tidak ada di daftar ceklis jenis produk) tetap ditolak.
        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), ['status' => 'unknown_stage'])
            ->assertSessionHasErrors('status');

        $this->assertSame(CustomOrder::STATUS_PENDING, $order->fresh()->status);

        // Lompat bebas: pending → QC (di luar urutan, tanpa gerbang prereq).
        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), ['status' => CustomOrder::STATUS_QC])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame(CustomOrder::STATUS_QC, $order->fresh()->status);
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

    public function test_first_production_stage_enterable_without_prereqs(): void
    {
        // Tanpa gerbang prereq: ukuran/harga/spesifikasi/DP tidak lagi
        // syarat masuk tahap produksi pertama — pilih status bebas.
        $kaos = $this->order(['status' => CustomOrder::STATUS_CONFIRMED]);

        $this->assertNull($kaos->resolvedTotalPrice());
        $this->assertNull($kaos->dp_amount);

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $kaos), ['status' => CustomOrder::STATUS_MATERIAL_PREP])
            ->assertRedirect(route('admin.orders.show', $kaos));

        $this->assertSame(CustomOrder::STATUS_MATERIAL_PREP, $kaos->fresh()->status);

        $jersey = $this->order([
            'status' => CustomOrder::STATUS_CONFIRMED,
            'service_type' => 'Printing Sublim - Jersey',
        ]);

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $jersey), ['status' => CustomOrder::STATUS_LAYOUT])
            ->assertRedirect(route('admin.orders.show', $jersey));

        $this->assertSame(CustomOrder::STATUS_LAYOUT, $jersey->fresh()->status);
    }

    public function test_move_back_to_design_resets_approval(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_LAYOUT]);
        $order->forceFill(['design_approved_at' => now()])->save();

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), ['status' => CustomOrder::STATUS_DESIGN])
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

    public function test_status_checklist_is_product_specific_and_freely_pickable(): void
    {
        $jersey = $this->order([
            'status' => CustomOrder::STATUS_CONFIRMED,
            'service_type' => 'Printing Sublim - Jersey',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $jersey))
            ->assertOk()
            ->assertSee('Tahap Jersey')
            ->assertSee('pilih bebas, tidak harus berurutan')
            ->assertSee('Saat ini')
            ->assertSee('value="layout"', false)
            ->assertSee('value="jahit"', false)
            ->assertSee('value="qc"', false)
            ->assertDontSee('value="material_prep"', false);

        $kaos = $this->order(['status' => CustomOrder::STATUS_CONFIRMED]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $kaos))
            ->assertOk()
            ->assertSee('Tahap Kaos &amp; Lainnya', false)
            ->assertSee('value="material_prep"', false)
            ->assertSee('value="cetak"', false)
            ->assertDontSee('value="jahit"', false);

        // Pending: radio tahap pertama (Terkonfirmasi) otomatis tercentang.
        $pending = $this->order(['status' => CustomOrder::STATUS_PENDING]);
        $html = $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $pending))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/value="confirmed"\s+checked/', $html);
    }

    public function test_completed_and_cancelled_have_final_state_in_show(): void
    {
        $completed = $this->order(['status' => CustomOrder::STATUS_COMPLETED]);

        // Completed final: ceklis & form batal disembunyikan.
        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $completed))
            ->assertOk()
            ->assertSee('Perbarui Status')
            ->assertSee('sudah final')
            ->assertDontSee('Batalkan Pesanan');

        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $completed))
            ->patch(route('admin.orders.status', $completed), ['status' => CustomOrder::STATUS_DESIGN])
            ->assertSessionHasErrors('status');

        $this->assertSame(CustomOrder::STATUS_COMPLETED, $completed->fresh()->status);

        $cancelled = $this->order(['status' => CustomOrder::STATUS_CANCELLED]);
        $cancelled->forceFill(['cancel_reason' => 'Alasan uji coba.'])->save();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $cancelled))
            ->assertOk()
            ->assertSee('sudah final')
            ->assertSee('Alasan uji coba.');
    }
}
