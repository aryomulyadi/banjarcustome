<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomOrder;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackOrderTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attributes = []): CustomOrder
    {
        $category = Category::create(['name' => 'Kaos Polos', 'slug' => 'kaos-polos']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Kaos Polos Cotton',
            'slug' => 'kaos-polos-cotton',
        ]);

        $order = CustomOrder::create(array_merge([
            'product_id' => $product->id,
            'name' => 'Andi Wijaya',
            'whatsapp_number' => '081234567890',
            'quantity' => 5,
            'order_details' => 'Kaos polos lengan pendek ukuran L, sablon dada kiri.',
        ], $attributes));

        if (array_key_exists('status', $attributes)) {
            $order->forceFill(['status' => $attributes['status']])->save();
        }

        return $order->refresh();
    }

    public function test_track_page_returns_successful_response(): void
    {
        $this->get('/cek-pesanan')->assertOk()->assertSee('Cek Status Pesanan');
    }

    public function test_track_link_is_visible_in_footer_and_success_page(): void
    {
        $this->get('/')->assertSee('Cek Status Pesanan');

        $order = $this->order();

        $this->get($order->successUrl())->assertOk()->assertSee('lacak status pesanan');
    }

    public function test_matching_order_id_and_phone_shows_status(): void
    {
        $order = $this->order(['status' => CustomOrder::STATUS_PRODUCTION]);

        $response = $this->post('/cek-pesanan', [
            'order_id' => $order->id,
            'whatsapp_number' => '081234567890',
        ]);

        $response->assertOk()
            ->assertSee('Sedang Diproses')
            ->assertSee('Kaos Polos Cotton')
            ->assertSee('081234567890');
    }

    public function test_wrong_phone_is_rejected_without_leaking_details(): void
    {
        $order = $this->order();

        $this->from('/cek-pesanan')
            ->followingRedirects()
            ->post('/cek-pesanan', [
                'order_id' => $order->id,
                'whatsapp_number' => '089999999999',
            ])
            ->assertOk()
            ->assertSee('Nomor order atau nomor WhatsApp tidak cocok.')
            ->assertDontSee('Sedang Diproses')
            ->assertDontSee('Kaos Polos Cotton');
    }

    public function test_unknown_order_id_is_rejected(): void
    {
        $this->order();

        $this->from('/cek-pesanan')->post('/cek-pesanan', [
            'order_id' => 9999,
            'whatsapp_number' => '081234567890',
        ])->assertRedirect('/cek-pesanan')->assertSessionHasErrors('order_id');
    }

    public function test_alternate_phone_formats_still_match(): void
    {
        $order = $this->order(['whatsapp_number' => '+62 812-3456-7890']);

        foreach (['081234567890', '6281234567890', '+62 812 3456 7890'] as $phone) {
            $this->post('/cek-pesanan', [
                'order_id' => $order->id,
                'whatsapp_number' => $phone,
            ])->assertOk()->assertSee('Kaos Polos Cotton');
        }
    }

    public function test_invalid_phone_format_is_rejected(): void
    {
        $order = $this->order();

        $this->from('/cek-pesanan')->post('/cek-pesanan', [
            'order_id' => $order->id,
            'whatsapp_number' => 'bukan nomor!',
        ])->assertRedirect('/cek-pesanan')->assertSessionHasErrors('whatsapp_number');
    }
}
