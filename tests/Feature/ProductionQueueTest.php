<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionQueueTest extends TestCase
{
    use RefreshDatabase;

    private function produksi(): User
    {
        return User::factory()->create([
            'name' => 'Operator Produksi',
            'role' => User::ROLE_PRODUKSI,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function order(array $attributes = []): CustomOrder
    {
        $category = Category::firstOrCreate(['slug' => 'sablon'], ['name' => 'Sablon']);
        $product = Product::firstOrCreate(['slug' => 'kaos-tes'], [
            'category_id' => $category->id,
            'title' => 'Kaos Tes',
        ]);

        return CustomOrder::forceCreate(array_merge([
            'product_id' => $product->id,
            'name' => 'Pelanggan Antrian',
            'whatsapp_number' => '081122233344',
            'order_details' => 'Detail pesanan untuk pengujian antrian produksi.',
        ], $attributes));
    }

    public function test_produksi_can_login_and_is_redirected_to_the_queue(): void
    {
        $user = $this->produksi();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.produksi'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_intended_admin_dashboard_falls_back_to_the_queue(): void
    {
        $user = $this->produksi();

        $this->get('/admin');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.produksi'));
    }

    public function test_guest_is_redirected_to_login_from_the_queue(): void
    {
        $this->get('/admin/produksi')->assertRedirect(route('login'));
    }

    public function test_regular_user_gets_403_on_the_queue(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/admin/produksi')->assertForbidden();
    }

    public function test_queue_lists_only_ready_and_production_orders(): void
    {
        $ready = $this->order([
            'status' => CustomOrder::STATUS_READY,
            'name' => 'Pelanggan Siap Produksi',
        ]);
        $running = $this->order([
            'status' => CustomOrder::STATUS_PRODUCTION,
            'name' => 'Pelanggan Sedang Diproses',
        ]);
        $this->order([
            'status' => CustomOrder::STATUS_PENDING,
            'name' => 'Pelanggan Menunggu Konfirmasi',
        ]);
        $this->order([
            'status' => CustomOrder::STATUS_CANCELLED,
            'name' => 'Pelanggan Dibatalkan',
        ]);

        $this->actingAs($this->produksi())->get('/admin/produksi')
            ->assertOk()
            ->assertSee('Pelanggan Siap Produksi')
            ->assertSee('Pelanggan Sedang Diproses')
            ->assertDontSee('Pelanggan Menunggu Konfirmasi')
            ->assertDontSee('Pelanggan Dibatalkan')
            ->assertSee('#'.$ready->id)
            ->assertSee('#'.$running->id);
    }

    public function test_queue_status_filter_shows_only_the_requested_status(): void
    {
        $this->order([
            'status' => CustomOrder::STATUS_READY,
            'name' => 'Pelanggan Siap Produksi',
        ]);
        $this->order([
            'status' => CustomOrder::STATUS_PRODUCTION,
            'name' => 'Pelanggan Sedang Diproses',
        ]);

        $this->actingAs($this->produksi())->get('/admin/produksi?status=production')
            ->assertOk()
            ->assertSee('Pelanggan Sedang Diproses')
            ->assertDontSee('Pelanggan Siap Produksi');
    }

    public function test_queue_mine_filter_matches_the_operator_name(): void
    {
        $this->order([
            'status' => CustomOrder::STATUS_PRODUCTION,
            'name' => 'Pesanan Milik Operator',
            'assigned_operator' => 'Operator Produksi',
        ]);
        $this->order([
            'status' => CustomOrder::STATUS_PRODUCTION,
            'name' => 'Pesanan Milik Orang Lain',
            'assigned_operator' => 'Pak Rusdi',
        ]);

        $this->actingAs($this->produksi())->get('/admin/produksi?status=production&milik=1')
            ->assertOk()
            ->assertSee('Pesanan Milik Operator')
            ->assertDontSee('Pesanan Milik Orang Lain');
    }

    public function test_produksi_can_open_detail_without_admin_only_cards(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PRODUCTION]);

        $this->actingAs($this->produksi())->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Progres Produksi')
            ->assertSee('Serah Terima Produksi')
            ->assertSee('Kartu Kerja')
            ->assertSee('Kembali ke antrian produksi')
            ->assertDontSee('Cetak Nota')
            ->assertDontSee('Spesifikasi Nota')
            ->assertDontSee('Perbarui Status')
            ->assertDontSee('Simpan Harga')
            ->assertDontSee('Simpan DP')
            ->assertDontSee('Pesan WhatsApp')
            ->assertDontSee('Kembali ke daftar pesanan');
    }

    public function test_admin_sees_all_cards_on_the_detail_page(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PRODUCTION]);

        $this->actingAs($this->admin())->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Cetak Nota')
            ->assertSee('Spesifikasi Nota')
            ->assertSee('Perbarui Status')
            ->assertSee('Simpan Harga')
            ->assertSee('Simpan DP')
            ->assertSee('Pesan WhatsApp')
            ->assertSee('Kembali ke daftar pesanan');
    }

    public function test_produksi_can_toggle_progress_from_detail_and_queue(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PRODUCTION]);
        $user = $this->produksi();

        $this->actingAs($user)->patch(route('admin.orders.progress', $order), [
            'produksi_ok' => '1',
            'redirect' => 'queue',
        ])->assertRedirect(route('admin.produksi'));

        $this->assertTrue($order->fresh()->produksi_ok);

        $this->actingAs($user)->patch(route('admin.orders.progress', $order), [
            'pesan_bahan_ok' => '1',
            'finish_ok' => '1',
        ])->assertRedirect(route('admin.orders.show', $order));

        $fresh = $order->fresh();
        $this->assertTrue($fresh->pesan_bahan_ok);
        $this->assertTrue($fresh->finish_ok);
    }

    public function test_produksi_can_open_work_card_and_spk(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PRODUCTION]);
        $user = $this->produksi();

        $this->actingAs($user)->get(route('admin.orders.card', $order))->assertOk();
        $this->actingAs($user)->get(route('admin.orders.spk', $order))->assertOk();
    }

    public function test_produksi_is_forbidden_from_admin_only_routes(): void
    {
        $order = $this->order();
        $user = $this->produksi();

        foreach ([
            route('admin.dashboard'),
            route('admin.orders.index'),
            route('admin.orders.export'),
            route('admin.orders.import'),
            route('admin.orders.print', $order),
            route('admin.orders.design', $order),
            route('admin.products.index'),
            route('admin.calculator'),
            route('admin.categories.index'),
            route('admin.master.index'),
            route('admin.galleries.index'),
            route('admin.faqs.index'),
            route('admin.testimonials.index'),
            route('admin.settings.edit'),
        ] as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    public function test_produksi_can_access_profile_and_the_admin_can_open_the_queue(): void
    {
        $this->actingAs($this->produksi())->get(route('admin.profile.edit'))->assertOk();

        $this->order(['status' => CustomOrder::STATUS_READY]);

        $this->actingAs($this->admin())->get('/admin/produksi')
            ->assertOk()
            ->assertSee('Antrian Produksi');
    }

    public function test_sidebar_shows_queue_and_hides_admin_menus_for_produksi(): void
    {
        $this->actingAs($this->produksi())->get('/admin/produksi')
            ->assertOk()
            ->assertSee('Antrian Produksi')
            ->assertDontSee('Data Master')
            ->assertDontSee('Pengaturan');

        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee('Antrian Produksi')
            ->assertSee('Data Master')
            ->assertSee('Pengaturan');
    }

    public function test_public_header_shows_queue_link_for_produksi(): void
    {
        $this->actingAs($this->produksi())->get('/')
            ->assertOk()
            ->assertSee('Antrian Produksi')
            ->assertDontSee('Dashboard Admin');
    }
}
