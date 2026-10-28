<?php

namespace Tests\Feature;

use App\Models\MasterOption;
use App\Models\TargetData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TargetDataTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function produksi(): User
    {
        return User::factory()->create(['role' => 'produksi']);
    }

    private function seedMaster(): void
    {
        foreach (['JERSEY', 'PDH', 'KAOS'] as $name) {
            MasterOption::create(['kind' => MasterOption::KIND_TARGET_LINE, 'name' => $name]);
        }
        foreach (['PENDEK-PANJANG', 'PANJANG SANGHAI ZIPPER'] as $name) {
            MasterOption::create(['kind' => MasterOption::KIND_TARGET_TYPE, 'name' => $name]);
        }
        MasterOption::create(['kind' => MasterOption::KIND_MATERIAL, 'name' => 'DRYFIT MILANO']);
        MasterOption::create(['kind' => MasterOption::KIND_COLOR, 'name' => 'FULL COLOUR']);
    }

    private function entry(array $overrides = []): TargetData
    {
        return TargetData::create(array_merge([
            'customer' => 'ARBI PAIJO 1810',
            'jenis' => 'JERSEY',
            'waktu_pengambilan' => '2026-10-24',
            'type' => 'PENDEK-PANJANG',
            'bahan' => 'DRYFIT MILANO',
            'warna' => 'FULL COLOUR',
            'pesanan_bahan' => 'OK',
            'progres_produksi' => 'NOK',
            'finish' => 'NOK',
            'total' => 24,
        ], $overrides));
    }

    public function test_guest_is_redirected_and_produksi_is_forbidden(): void
    {
        $this->seedMaster();
        $entry = $this->entry();

        $this->get(route('admin.target.index'))->assertRedirect(route('login'));
        $this->get(route('admin.target.create'))->assertRedirect(route('login'));

        $this->actingAs($this->produksi())->get(route('admin.target.index'))->assertForbidden();
        $this->actingAs($this->produksi())->get(route('admin.target.edit', $entry))->assertForbidden();
    }

    public function test_index_page_shows_toolbar_and_empty_state(): void
    {
        $this->seedMaster();

        $this->actingAs($this->admin())
            ->get(route('admin.target.index'))
            ->assertOk()
            ->assertSee('Data Target / Produksi')
            ->assertSee('Belum ada data target', false);
    }

    public function test_target_entry_can_be_created_edited_and_deleted(): void
    {
        $this->seedMaster();

        $this->actingAs($this->admin())
            ->post(route('admin.target.store'), [
                'customer' => 'ARBI PAIJO 1810',
                'jenis' => 'JERSEY',
                'waktu_pengambilan' => '2026-10-24',
                'type' => 'PENDEK-PANJANG',
                'bahan' => 'DRYFIT MILANO',
                'warna' => 'FULL COLOUR',
                'pesanan_bahan' => 'OK',
                'progres_produksi' => 'NOK',
                'finish' => 'NOK',
                'total' => 24,
            ])
            ->assertRedirect(route('admin.target.index'));

        $entry = TargetData::firstOrFail();
        $this->assertSame(24, $entry->total);
        $this->assertSame('OK', $entry->pesanan_bahan);

        $this->actingAs($this->admin())
            ->put(route('admin.target.update', $entry), [
                'customer' => 'ARBI PAIJO 1810',
                'jenis' => 'PDH',
                'waktu_pengambilan' => null,
                'type' => null,
                'bahan' => null,
                'warna' => null,
                'pesanan_bahan' => 'OK',
                'progres_produksi' => 'OK',
                'finish' => 'OK',
                'total' => 30,
            ])
            ->assertRedirect(route('admin.target.index'));

        $fresh = $entry->fresh();
        $this->assertSame('PDH', $fresh->jenis);
        $this->assertSame('OK', $fresh->progres_produksi);
        $this->assertSame(30, $fresh->total);

        $this->actingAs($this->admin())
            ->delete(route('admin.target.destroy', $entry))
            ->assertRedirect(route('admin.target.index'));

        $this->assertDatabaseCount('target_data', 0);
    }

    public function test_store_validates_flags_and_total(): void
    {
        $this->seedMaster();

        $this->actingAs($this->admin())
            ->from(route('admin.target.index'))
            ->post(route('admin.target.store'), [
                'customer' => '',
                'pesanan_bahan' => 'MAYBE',
                'progres_produksi' => 'MAYBE',
                'finish' => 'MAYBE',
                'total' => -5,
            ])
            ->assertRedirect(route('admin.target.index'))
            ->assertSessionHasErrors(['customer', 'pesanan_bahan', 'progres_produksi', 'finish', 'total']);
    }

    public function test_index_search_filter_and_sort(): void
    {
        $this->seedMaster();
        $this->entry(['customer' => 'BUDI 99', 'jenis' => 'PDH', 'pesanan_bahan' => 'NOK', 'total' => 10]);
        $this->entry(['customer' => 'CEDEK 1', 'jenis' => 'KAOS', 'pesanan_bahan' => 'OK', 'total' => 99]);

        $this->actingAs($this->admin())
            ->get(route('admin.target.index', ['q' => 'BUDI']))
            ->assertOk()
            ->assertSee('BUDI 99')
            ->assertDontSee('CEDEK 1');

        $this->actingAs($this->admin())
            ->get(route('admin.target.index', ['jenis' => 'PDH']))
            ->assertOk()
            ->assertSee('BUDI 99')
            ->assertDontSee('CEDEK 1');

        $this->actingAs($this->admin())
            ->get(route('admin.target.index', ['pesanan_bahan' => 'NOK']))
            ->assertOk()
            ->assertSee('BUDI 99')
            ->assertDontSee('CEDEK 1');

        $html = $this->actingAs($this->admin())
            ->get(route('admin.target.index', ['sort' => 'total:desc']))
            ->assertOk()
            ->getContent();

        $this->assertLessThan(strpos($html, 'BUDI 99'), strpos($html, 'CEDEK 1'));
    }

    public function test_form_shows_master_dropdown_options_and_flags(): void
    {
        $this->seedMaster();

        $this->actingAs($this->admin())
            ->get(route('admin.target.create'))
            ->assertOk()
            ->assertSee('JERSEY')
            ->assertSee('PENDEK-PANJANG')
            ->assertSee('DRYFIT MILANO')
            ->assertSee('FULL COLOUR')
            ->assertSee('Pesan Bahan')
            ->assertSee('Produksi');
    }
}
