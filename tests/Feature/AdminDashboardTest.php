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

class AdminDashboardTest extends TestCase
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
            'name' => 'Pelanggan Uji',
            'whatsapp_number' => '081111111111',
            'order_details' => 'Detail pesanan untuk pengujian dashboard admin.',
        ], $attributes));

        if (array_key_exists('status', $attributes)) {
            $order->forceFill(['status' => $attributes['status']])->save();
        }

        return $order->refresh();
    }

    public function test_guest_is_redirected_from_all_admin_pages(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.orders.index'))->assertRedirect(route('login'));
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));
        $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
        $this->get(route('admin.galleries.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_gets_403_everywhere(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.orders.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.products.index'))->assertForbidden();
    }

    public function test_dashboard_shows_stats_and_recent_orders(): void
    {
        $this->order(['status' => CustomOrder::STATUS_PENDING]);
        $this->order(['status' => CustomOrder::STATUS_PRODUCTION]);
        $this->order(['status' => CustomOrder::STATUS_CANCELLED]);

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Menunggu Konfirmasi')
            ->assertSee('Pesanan Terbaru')
            ->assertSee('Pelanggan Uji')
            ->assertSee('Dibatalkan')
            ->assertSee('status=cancelled', false)
            ->assertSee('Pesanan 30 Hari Terakhir')
            ->assertSee('aria-label="Grafik batang jumlah pesanan masuk per hari selama 30 hari terakhir"', false)
            ->assertSee('<script nonce="', false);
    }

    public function test_pending_orders_show_badge_in_admin_sidebar(): void
    {
        $this->order(['status' => CustomOrder::STATUS_PENDING]);
        $this->order(['status' => CustomOrder::STATUS_PENDING]);

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('2 pesanan menunggu konfirmasi');
    }

    public function test_express_order_shows_badge_on_detail_print_and_export(): void
    {
        $order = $this->order();
        $order->forceFill(['is_express' => true])->save();

        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Express');

        $this->actingAs($admin)
            ->get(route('admin.orders.print', $order))
            ->assertOk()
            ->assertSee('EXPRESS — same-day / di bawah 10 hari');

        $csv = $this->actingAs($admin)->get(route('admin.orders.export'))->streamedContent();
        $this->assertStringContainsString('Express', $csv);
    }

    public function test_orders_index_filters_by_status(): void
    {
        $this->order(['status' => CustomOrder::STATUS_PENDING, 'name' => 'Pesanan Pending']);
        $this->order(['status' => CustomOrder::STATUS_COMPLETED, 'name' => 'Pesanan Selesai']);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('Pesanan Selesai')
            ->assertDontSee('Pesanan Pending');
    }

    public function test_orders_index_searches_by_name(): void
    {
        $this->order(['name' => 'Budi Kurniawan']);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['q' => 'Budi']))
            ->assertOk()
            ->assertSee('Budi Kurniawan');

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['q' => 'TidakAda']))
            ->assertOk()
            ->assertSee('Tidak ada pesanan yang cocok.');
    }

    public function test_admin_can_view_order_detail_and_update_status(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PENDING]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Detail Pesanan')
            ->assertSee('Pelanggan Uji');

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'production'])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame(CustomOrder::STATUS_PRODUCTION, $order->fresh()->status);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $order = $this->order();

        $this->actingAs($this->admin())
            ->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), ['status' => 'batal'])
            ->assertSessionHasErrors('status');

        $this->assertSame(CustomOrder::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_admin_can_cancel_order(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PENDING]);

        $this->actingAs($this->admin())
            ->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame(CustomOrder::STATUS_CANCELLED, $order->fresh()->status);
    }

    public function test_orders_index_filters_by_cancelled_status(): void
    {
        $this->order(['status' => CustomOrder::STATUS_PENDING, 'name' => 'Pesanan Berjalan']);
        $this->order(['status' => CustomOrder::STATUS_CANCELLED, 'name' => 'Pesanan Dibatalkan']);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index', ['status' => 'cancelled']))
            ->assertOk()
            ->assertSee('Pesanan Dibatalkan')
            ->assertDontSee('Pesanan Berjalan');
    }

    public function test_admin_can_download_design_file(): void
    {
        Storage::fake('local');

        $order = $this->order();
        $path = UploadedFile::fake()->create('desain-mentah.pdf', 50, 'application/pdf')
            ->storeAs('designs', 'desain-mentah.pdf', 'local');
        $order->update(['design_file' => $path]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.design', $order))
            ->assertOk()
            ->assertDownload();
    }

    public function test_download_returns_404_when_file_missing(): void
    {
        Storage::fake('local');

        $order = $this->order(['design_file' => 'designs/hilang.pdf']);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.design', $order))
            ->assertNotFound();
    }

    public function test_download_returns_404_without_design_file(): void
    {
        $order = $this->order();

        $this->actingAs($this->admin())
            ->get(route('admin.orders.design', $order))
            ->assertNotFound();
    }

    public function test_non_admin_cannot_download_design(): void
    {
        Storage::fake('local');

        $order = $this->order();
        $path = UploadedFile::fake()->create('rahasia.pdf', 10, 'application/pdf')
            ->storeAs('designs', 'rahasia.pdf', 'local');
        $order->update(['design_file' => $path]);

        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.orders.design', $order))
            ->assertForbidden();
    }
}
