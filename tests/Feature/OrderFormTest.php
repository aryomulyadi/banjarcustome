<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomOrder;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_form_page_returns_successful_response(): void
    {
        $response = $this->get('/pesan');

        $response->assertOk()->assertSee('Form Pemesanan Custom');
    }

    public function test_order_form_lists_service_types_from_config(): void
    {
        $response = $this->get('/pesan');

        $response->assertOk();

        foreach (config('banjarcustom.services') as $service) {
            $this->assertStringContainsString($service['title'], $response->getContent());
            $this->assertLessThanOrEqual(60, mb_strlen($service['title']), 'Judul layanan harus muat di kolom service_type (60 karakter).');
        }
    }

    public function test_order_form_is_prefilled_from_product_slug(): void
    {
        $category = Category::create(['name' => 'Sablon', 'slug' => 'sablon']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Kaos Promosi Custom',
            'slug' => 'kaos-promosi-custom',
        ]);

        $response = $this->get('/pesan?produk='.$product->slug);

        $response->assertOk()->assertSee('Kaos Promosi Custom');
    }

    public function test_order_requires_valid_fields(): void
    {
        $response = $this->from(route('pesan.create'))->post(route('pesan.store'), [
            'name' => '',
            'whatsapp_number' => 'bukan-nomor!',
            'order_details' => 'pendek',
        ]);

        $response->assertRedirect(route('pesan.create'))
            ->assertSessionHasErrors(['name', 'whatsapp_number', 'order_details']);

        $this->assertDatabaseCount('custom_orders', 0);
    }

    public function test_valid_order_is_stored_and_redirects_to_success(): void
    {
        $category = Category::create(['name' => 'Jersey Printing', 'slug' => 'jersey-printing']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Jersey Futsal Printing',
            'slug' => 'jersey-futsal-printing',
        ]);

        $response = $this->post(route('pesan.store'), [
            'name' => 'Budi Santoso',
            'whatsapp_number' => '081348138440',
            'product_id' => $product->id,
            'quantity' => 12,
            'order_details' => 'Jersey futsal warna biru, nama tim Garuda, deadline 2 minggu.',
        ]);

        $order = CustomOrder::first();

        $this->assertNotNull($order);
        $this->assertSame('Budi Santoso', $order->name);
        $this->assertSame($product->id, $order->product_id);
        $this->assertSame(12, $order->quantity);
        $this->assertFalse($order->is_express);
        $this->assertSame(CustomOrder::STATUS_PENDING, $order->status);
        $this->assertSame(CustomOrder::STATUS_PENDING, $order->statusHistory()->first()->to_status);

        $response->assertRedirect($order->successUrl());

        $this->get($order->successUrl())->assertOk()->assertSee('Pesanan Berhasil Dikirim!');
    }

    public function test_order_form_shows_estimation_hint_and_express_checkbox(): void
    {
        $this->get('/pesan')
            ->assertOk()
            ->assertSee('kaos 3–7 hari kerja, jersey 10–12 hari')
            ->assertSee('name="express"', false)
            ->assertSee('Pesanan Express')
            ->assertSee('same-day');
    }

    public function test_express_order_is_saved_and_admins_receive_notification(): void
    {
        Notification::fake();
        User::factory()->create(['role' => 'admin']);

        $response = $this->post(route('pesan.store'), [
            'name' => 'Pelanggan Express',
            'whatsapp_number' => '081234567890',
            'express' => '1',
            'order_details' => 'Butuh kaos promosi untuk acara akhir pekan ini.',
        ]);

        $order = CustomOrder::firstOrFail();

        $this->assertTrue($order->is_express);
        $response->assertRedirect($order->successUrl());

        Notification::assertSentOnDemand(NewOrderNotification::class);

        $this->get($order->successUrl())
            ->assertOk()
            ->assertSee('Express')
            ->assertSee('DP 50%');
    }

    public function test_success_page_requires_valid_token(): void
    {
        $order = $this->order();

        $this->get(route('pesan.success', ['token' => 'invalid-token']))->assertNotFound();
        $this->get(route('pesan.success', ['token' => str_repeat('a', 40)]))->assertNotFound();
        $this->get($order->successUrl())->assertOk();
    }

    public function test_success_page_hides_customer_data_from_unknown_token(): void
    {
        $order = $this->order();

        $this->get($order->successUrl())
            ->assertOk()
            ->assertSee('Budi Dalam Tes', false)
            ->assertSee($order->whatsapp_number);
    }

    public function test_sizes_sum_becomes_quantity(): void
    {
        $response = $this->post(route('pesan.store'), [
            'name' => 'Rina',
            'whatsapp_number' => '081234567890',
            'sizes' => ['S' => 2, 'M' => 3, 'L' => 5, 'XL' => 0],
            'order_details' => 'Kaos komunitas ukuran campur sesuai grid ukuran.',
        ]);

        $order = CustomOrder::first();

        $this->assertNotNull($order);
        $this->assertSame(10, $order->quantity);
        $this->assertSame(['S' => 2, 'M' => 3, 'L' => 5], $order->size_quantities);

        $response->assertRedirect($order->successUrl());
    }

    public function test_delivery_requires_address(): void
    {
        $response = $this->from(route('pesan.create'))->post(route('pesan.store'), [
            'name' => 'Pelanggan',
            'whatsapp_number' => '081234567890',
            'delivery_method' => 'kirim',
            'order_details' => 'Pesanan dikirim tanpa alamat lengkap pada tes ini.',
        ]);

        $response->assertRedirect(route('pesan.create'))->assertSessionHasErrors('address');
        $this->assertDatabaseCount('custom_orders', 0);
    }

    public function test_deadline_cannot_be_in_the_past(): void
    {
        $this->from(route('pesan.create'))->post(route('pesan.store'), [
            'name' => 'Pelanggan',
            'whatsapp_number' => '081234567890',
            'deadline' => now()->subDay()->toDateString(),
            'order_details' => 'Deadline lewat hari ini harus ditolak oleh validasi.',
        ])->assertSessionHasErrors('deadline');

        $this->assertDatabaseCount('custom_orders', 0);
    }

    public function test_honeypot_field_is_ignored(): void
    {
        $response = $this->post(route('pesan.store'), [
            'name' => 'Bot',
            'whatsapp_number' => '081234567890',
            'order_details' => 'Isian dari bot yang mengisi kolom tersembunyi.',
            'website' => 'http://spam.example',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseCount('custom_orders', 0);
    }

    public function test_order_form_is_rate_limited(): void
    {
        $payload = [
            'name' => 'Pelanggan',
            'whatsapp_number' => '081234567890',
            'order_details' => 'Pesan beruntun untuk menguji batas rate limit endpoint.',
        ];

        for ($i = 0; $i < 12; $i++) {
            $this->post(route('pesan.store'), $payload);
        }

        $this->post(route('pesan.store'), $payload)->assertStatus(429);
    }

    public function test_design_file_upload_is_stored(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('desain-tim.pdf', 200, 'application/pdf');

        $response = $this->post(route('pesan.store'), [
            'name' => 'Siti Aminah',
            'whatsapp_number' => '081234567890',
            'order_details' => 'Kaos komunitas dengan desain terlampir, ukuran M-L-XL campur.',
            'design_file' => $file,
        ]);

        $order = CustomOrder::first();

        $this->assertNotNull($order);
        $this->assertNotNull($order->design_file);
        $this->assertStringStartsWith('designs/', $order->design_file);
        Storage::disk('local')->assertExists($order->design_file);

        $response->assertRedirect($order->successUrl());
    }

    public function test_design_file_must_have_allowed_extension(): void
    {
        $file = UploadedFile::fake()->create('script.php', 10, 'application/x-httpd-php');

        $this->from(route('pesan.create'))->post(route('pesan.store'), [
            'name' => 'Pelanggan',
            'whatsapp_number' => '081234567890',
            'order_details' => 'Upload file dengan ekstensi yang tidak diizinkan.',
            'design_file' => $file,
        ])->assertSessionHasErrors('design_file');

        $this->assertDatabaseCount('custom_orders', 0);
    }

    public function test_order_rejects_oversized_design_file(): void
    {
        $file = UploadedFile::fake()->create('besar.zip', 6000);

        $response = $this->from(route('pesan.create'))->post(route('pesan.store'), [
            'name' => 'Pelanggan',
            'whatsapp_number' => '081234567890',
            'order_details' => 'File desain terlalu besar untuk diunggah pada tes ini.',
            'design_file' => $file,
        ]);

        $response->assertSessionHasErrors('design_file');
        $this->assertDatabaseCount('custom_orders', 0);
    }

    private function order(array $attributes = []): CustomOrder
    {
        $order = CustomOrder::create(array_merge([
            'name' => 'Budi Dalam Tes',
            'whatsapp_number' => '089999999999',
            'order_details' => 'Detail pesanan untuk halaman sukses pada pengujian.',
        ], $attributes));

        if (array_key_exists('status', $attributes)) {
            $order->forceFill(['status' => $attributes['status']])->save();
        }

        return $order->refresh();
    }
}
