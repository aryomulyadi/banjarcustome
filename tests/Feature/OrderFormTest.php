<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomOrder;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class OrderFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_form_page_returns_successful_response(): void
    {
        $response = $this->get('/pesan');

        $response->assertOk()->assertSee('Form Pemesanan Custom');
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
        $this->assertSame(CustomOrder::STATUS_PENDING, $order->status);

        $response->assertRedirect(route('pesan.success', $order));

        $this->get(route('pesan.success', $order))->assertOk()->assertSee('Pesanan Berhasil Dikirim!');
    }

    public function test_design_file_upload_is_stored(): void
    {
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

        $response->assertRedirect(route('pesan.success', $order));
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
}
