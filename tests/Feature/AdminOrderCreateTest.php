<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderCreateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_create_order_form(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.orders.create'))
            ->assertOk()
            ->assertSee('Buat Pesanan Baru')
            ->assertSee('Nama Pelanggan')
            ->assertSee('Nomor WhatsApp');
    }

    public function test_admin_can_create_order_manually(): void
    {
        $category = Category::create(['name' => 'Kaos', 'slug' => 'kaos']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Kaos Polo Eksklusif',
            'slug' => 'kaos-polo-eksklusif',
        ]);

        $response = $this->actingAs($this->admin())
            ->post(route('admin.orders.store'), [
                'name' => 'Ahmad Dani',
                'whatsapp_number' => '081234567890',
                'product_id' => $product->id,
                'quantity' => 20,
                'unit_price' => 50000,
                'total_price' => 1000000,
                'delivery_method' => 'ambil',
                'order_details' => 'Pesanan bordir logo dada kiri warna emas.',
            ]);

        $order = CustomOrder::where('name', 'Ahmad Dani')->firstOrFail();

        $response->assertRedirect(route('admin.orders.show', $order));

        $this->assertSame('081234567890', $order->whatsapp_number);
        $this->assertSame($product->id, $order->product_id);
        $this->assertSame('Kaos Polo Eksklusif', $order->service_type);
        $this->assertSame(20, $order->quantity);
        $this->assertSame(50000, $order->unit_price);
        $this->assertSame(1000000, $order->total_price);
    }

    public function test_admin_can_delete_order(): void
    {
        $order = CustomOrder::create([
            'name' => 'Pesanan Hapus',
            'whatsapp_number' => '081122334455',
            'quantity' => 10,
        ]);

        $this->assertDatabaseHas('custom_orders', ['id' => $order->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.orders.destroy', $order))
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('custom_orders', ['id' => $order->id]);
    }
}
