<?php

namespace Tests\Feature;

use App\Models\CustomOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFormMoqTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Pelanggan Jersey',
            'whatsapp_number' => '081348138440',
            'service_type' => 'Printing Sublim — Jersey',
            'order_details' => 'Pesanan jersey tim untuk uji MOQ minimal dua belas pcs.',
        ], $overrides);
    }

    public function test_jersey_below_moq_is_rejected(): void
    {
        $response = $this->from(route('pesan.create'))
            ->post(route('pesan.store'), $this->payload(['quantity' => 11]));

        $response->assertRedirect(route('pesan.create'))
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('custom_orders', 0);

        $message = session('errors')->first('quantity');
        $this->assertStringContainsString('Minimal pemesanan jersey 12 pcs (MOQ).', $message);
    }

    public function test_jersey_without_quantity_is_rejected(): void
    {
        $this->from(route('pesan.create'))
            ->post(route('pesan.store'), $this->payload())
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('custom_orders', 0);
    }

    public function test_jersey_sizes_sum_below_moq_is_rejected(): void
    {
        $this->from(route('pesan.create'))
            ->post(route('pesan.store'), $this->payload(['sizes' => ['S' => 5, 'M' => 6]]))
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('custom_orders', 0);
    }

    public function test_jersey_at_moq_is_stored(): void
    {
        $response = $this->post(route('pesan.store'), $this->payload(['quantity' => 12]));

        $order = CustomOrder::first();

        $this->assertNotNull($order);
        $this->assertSame(12, $order->quantity);
        $this->assertTrue($order->isJersey());

        $response->assertRedirect($order->successUrl());
    }

    public function test_non_jersey_below_moq_is_allowed(): void
    {
        $response = $this->post(route('pesan.store'), $this->payload([
            'service_type' => 'Sablon DTF',
            'quantity' => 1,
        ]));

        $order = CustomOrder::first();

        $this->assertNotNull($order);
        $this->assertSame(1, $order->quantity);

        $response->assertRedirect($order->successUrl());
    }
}
