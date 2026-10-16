<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomOrder;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrackDesignFlowTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attributes = []): CustomOrder
    {
        $category = Category::firstOrCreate(['slug' => 'sablon'], ['name' => 'Sablon']);
        $product = Product::firstOrCreate(['slug' => 'kaos-sablon'], [
            'category_id' => $category->id,
            'title' => 'Kaos Sablon',
        ]);

        $order = CustomOrder::create(array_merge([
            'product_id' => $product->id,
            'name' => 'Pelanggan Lacak',
            'whatsapp_number' => '081298765432',
            'order_details' => 'Detail pesanan untuk pengujian halaman lacak.',
        ], $attributes));

        if (array_key_exists('status', $attributes)) {
            $order->forceFill(['status' => $attributes['status']])->save();
        }

        return $order->refresh();
    }

    public function test_track_token_page_shows_status_and_timeline_without_admin_name(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PRODUCTION, 'deadline' => now()->addDays(3)]);

        $this->get(route('track.token', ['token' => $order->tracking_token]))
            ->assertOk()
            ->assertSee('Riwayat Pesanan')
            ->assertSee($order->statusLabel())
            ->assertSee('Menunggu Konfirmasi')
            ->assertDontSee('Riwayat Status');
    }

    public function test_customer_can_approve_design_from_track_page(): void
    {
        Storage::fake('local');

        $order = $this->order(['status' => CustomOrder::STATUS_DESIGN]);
        $path = UploadedFile::fake()->create('desain-admin.jpg', 100, 'image/jpeg')
            ->storeAs('designs/admin', 'desain-admin.jpg', 'local');
        $order->forceFill(['design_file_admin' => $path])->save();

        $token = $order->tracking_token;

        $this->get(route('track.token', ['token' => $token]))
            ->assertOk()
            ->assertSee('Persetujuan Desain')
            ->assertSee('Lihat / Unduh Desain');

        $this->post(route('track.approve', ['token' => $token]))
            ->assertRedirect(route('track.token', ['token' => $token]));

        $this->assertNotNull($order->fresh()->design_approved_at);

        $this->get(route('track.design', ['token' => $token]))
            ->assertOk()
            ->assertDownload();
    }

    public function test_approve_rejected_outside_design_stage(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PENDING]);

        $this->from(route('track.token', ['token' => $order->tracking_token]))
            ->post(route('track.approve', ['token' => $order->tracking_token]))
            ->assertRedirect(route('track.token', ['token' => $order->tracking_token]));

        $this->assertNull($order->fresh()->design_approved_at);
        $this->assertNotNull(session('track_error'));
    }

    public function test_customer_can_request_revision_from_ready(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_READY]);
        $order->forceFill(['design_approved_at' => now(), 'design_revisions' => 1])->save();

        $token = $order->tracking_token;

        $this->post(route('track.revision', ['token' => $token]), [
            'revision_reason' => 'Nama pemain diganti menjadi ARDI.',
        ])->assertRedirect(route('track.token', ['token' => $token]));

        $fresh = $order->fresh();

        $this->assertSame(CustomOrder::STATUS_DESIGN, $fresh->status);
        $this->assertNull($fresh->design_approved_at);
        $this->assertSame(2, $fresh->design_revisions);
        $this->assertSame('Nama pemain diganti menjadi ARDI.', $fresh->design_revision_reason);

        $last = $fresh->statusHistory()->latest('id')->first();
        $this->assertSame(CustomOrder::STATUS_READY, $last->from_status);
        $this->assertSame(CustomOrder::STATUS_DESIGN, $last->to_status);
        $this->assertNull($last->changed_by);
    }

    public function test_revision_requires_reason(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_DESIGN]);

        $this->from(route('track.token', ['token' => $order->tracking_token]))
            ->post(route('track.revision', ['token' => $order->tracking_token]), [
                'revision_reason' => 'abc',
            ])
            ->assertSessionHasErrors('revision_reason');

        $this->assertSame(0, $order->fresh()->design_revisions);
    }

    public function test_revision_rejected_in_production(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PRODUCTION]);

        $this->from(route('track.token', ['token' => $order->tracking_token]))
            ->post(route('track.revision', ['token' => $order->tracking_token]), [
                'revision_reason' => 'Minta ubah warna.',
            ])
            ->assertRedirect(route('track.token', ['token' => $order->tracking_token]));

        $this->assertSame(CustomOrder::STATUS_PRODUCTION, $order->fresh()->status);
        $this->assertNotNull(session('track_error'));
    }

    public function test_unknown_token_is_404(): void
    {
        $this->get(route('track.token', ['token' => str_repeat('a', 40)]))->assertNotFound();
        $this->get(route('track.design', ['token' => str_repeat('a', 40)]))->assertNotFound();
    }

    public function test_track_form_result_shows_approval_section(): void
    {
        Storage::fake('local');

        $order = $this->order(['status' => CustomOrder::STATUS_DESIGN]);
        $path = UploadedFile::fake()->create('desain-form.jpg', 50, 'image/jpeg')
            ->storeAs('designs/admin', 'desain-form.jpg', 'local');
        $order->forceFill(['design_file_admin' => $path])->save();

        $this->post(route('track.store'), [
            'order_id' => $order->id,
            'whatsapp_number' => '0812-9876-5432',
        ])->assertOk()->assertSee('Persetujuan Desain');
    }
}
