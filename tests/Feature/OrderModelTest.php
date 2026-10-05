<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomOrder;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_order_gets_unique_forty_char_token_and_initial_history(): void
    {
        $first = CustomOrder::create(['name' => 'A', 'whatsapp_number' => '081111111111']);
        $second = CustomOrder::create(['name' => 'B', 'whatsapp_number' => '082222222222']);

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{40}$/', $first->tracking_token);
        $this->assertNotSame($first->tracking_token, $second->tracking_token);
        $this->assertSame(1, $first->statusHistory()->count());
        $this->assertNull($first->statusHistory()->first()->from_status);
        $this->assertSame(CustomOrder::STATUS_PENDING, $first->statusHistory()->first()->to_status);
    }

    public function test_default_status_is_pending_and_labels_are_translated(): void
    {
        $order = CustomOrder::create(['name' => 'A', 'whatsapp_number' => '081111111111']);

        $this->assertSame(CustomOrder::STATUS_PENDING, $order->status);
        $this->assertSame('Menunggu Konfirmasi', $order->statusLabel());
        $this->assertSame('Sedang Diproses', CustomOrder::STATUS_LABELS[CustomOrder::STATUS_PRODUCTION]);
        $this->assertSame(
            'status-aneh',
            (new CustomOrder)->forceFill(['status' => 'status-aneh'])->statusLabel()
        );
    }

    public function test_status_is_not_mass_assignable(): void
    {
        $order = CustomOrder::create([
            'name' => 'A',
            'whatsapp_number' => '081111111111',
            'status' => CustomOrder::STATUS_COMPLETED,
        ]);

        $this->assertSame(CustomOrder::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_success_url_points_to_token_route(): void
    {
        $order = CustomOrder::create(['name' => 'A', 'whatsapp_number' => '081111111111']);

        $this->assertSame(
            url('/pesan/sukses/'.$order->tracking_token),
            $order->successUrl()
        );
    }

    public function test_whatsapp_link_contains_order_summary(): void
    {
        $category = Category::create(['name' => 'Distro', 'slug' => 'distro']);
        $product = Product::create(['category_id' => $category->id, 'title' => 'Kaos Distro', 'slug' => 'kaos-distro']);

        $order = CustomOrder::create([
            'product_id' => $product->id,
            'name' => 'Dewi',
            'whatsapp_number' => '081234567890',
            'quantity' => 24,
            'deadline' => now()->addDays(7)->toDateString(),
            'service_type' => 'Sablon Plastisol',
            'delivery_method' => 'kirim',
            'address' => 'Jl. Contoh No. 1',
            'size_quantities' => ['M' => 12, 'L' => 12],
            'order_details' => 'Kaos komunitas lengan panjang.',
            'notes' => 'Sablon depan belakang.',
        ]);

        $link = $order->whatsappLink();

        $this->assertStringStartsWith(config('banjarcustom.whatsapp_link').'?text=', $link);
        $this->assertStringContainsString(rawurlencode('Kaos Distro'), $link);
        $this->assertStringContainsString(rawurlencode('24 pcs'), $link);
        $this->assertStringContainsString(rawurlencode('M: 12, L: 12'), $link);
        $this->assertStringContainsString(rawurlencode('Sablon Plastisol'), $link);
        $this->assertStringContainsString(rawurlencode('Jl. Contoh No. 1'), $link);
        $this->assertStringContainsString(rawurlencode('081234567890'), $link);
        $this->assertStringContainsString(rawurlencode('Sablon depan belakang.'), $link);
    }

    public function test_phone_matching_accepts_common_formats(): void
    {
        $order = new CustomOrder(['whatsapp_number' => '+62 812-3456-7890']);

        $this->assertTrue($order->matchesPhone('081234567890'));
        $this->assertTrue($order->matchesPhone('6281234567890'));
        $this->assertTrue($order->matchesPhone('+62 812 3456 7890'));
        $this->assertFalse($order->matchesPhone('089999999999'));
        $this->assertSame('81234567890', CustomOrder::normalizePhone('0812 3456 7890'));
    }
}
