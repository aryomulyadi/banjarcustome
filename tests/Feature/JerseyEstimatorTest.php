<?php

namespace Tests\Feature;

use App\Models\MasterOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JerseyEstimatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_katalog_shows_estimator_card_with_options_and_min_order(): void
    {
        $minOrder = (int) config('banjarcustom.jersey.min_order', 12);

        $this->get(route('katalog-jersey'))
            ->assertOk()
            ->assertSee('Kalkulator Estimasi Jersey')
            ->assertSee('Estimasi Total')
            ->assertSee('Pesan via WhatsApp')
            ->assertSee('Jumlah minimal '.$minOrder.' pcs untuk pemesanan jersey.')
            ->assertSee('min="'.$minOrder.'"', false)
            ->assertSee('get unitTotal', false)
            ->assertSee('get waHref', false)
            ->assertSee('Wangky Zipper')
            ->assertSee('Dryfit Milano');
    }

    public function test_wa_default_link_is_prefilled_with_specification_and_default_total(): void
    {
        $basePrice = (int) config('banjarcustom.jersey.base_price', 85000);
        $minOrder = (int) config('banjarcustom.jersey.min_order', 12);
        $defaultTotal = number_format($basePrice * $minOrder, 0, ',', '.');

        $this->get(route('katalog-jersey'))
            ->assertOk()
            ->assertSee(rawurlencode('Halo Banjar Custome, saya mau pesan jersey dari Katalog Jersey:'), false)
            ->assertSee(rawurlencode('- Jumlah: '.$minOrder.' pcs'), false)
            ->assertSee(rawurlencode('Estimasi total: Rp '.$defaultTotal), false);
    }

    public function test_estimator_select_and_payload_use_active_db_options(): void
    {
        MasterOption::create([
            'kind' => MasterOption::KIND_COLLAR,
            'name' => 'Kerah Estimasi',
            'fee' => 4500,
            'active' => true,
            'sort' => 0,
        ]);
        MasterOption::create([
            'kind' => MasterOption::KIND_COLLAR,
            'name' => 'Kerah disembunyikan',
            'fee' => 9999,
            'active' => false,
            'sort' => 1,
        ]);

        $response = $this->get(route('katalog-jersey'));

        $response->assertOk()
            ->assertSee('Kerah Estimasi')
            ->assertSee('(+4.500)')
            ->assertSee('&quot;fee&quot;:4500', false)
            ->assertDontSee('Kerah disembunyikan');

        // Jenis bahan masih kosong di DB -> fallback config tetap dipakai.
        $response->assertSee('Brazil Bintik');
    }

    public function test_estimator_is_not_shown_on_other_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Kalkulator Estimasi Jersey');
    }
}
