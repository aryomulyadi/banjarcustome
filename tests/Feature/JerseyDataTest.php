<?php

namespace Tests\Feature;

use App\Models\JerseyData;
use App\Models\MasterOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JerseyDataTest extends TestCase
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
        foreach (['AAN', 'ARYO', 'IMUL'] as $name) {
            MasterOption::create(['kind' => MasterOption::KIND_DESIGNER, 'name' => $name]);
        }
        MasterOption::create(['kind' => MasterOption::KIND_COLLAR, 'name' => 'O-Neck']);
        MasterOption::create(['kind' => MasterOption::KIND_FABRIC, 'name' => 'Milano']);
        MasterOption::create(['kind' => MasterOption::KIND_JERSEY_MODEL, 'name' => 'Pendek']);
    }

    private function entry(array $overrides = []): JerseyData
    {
        return JerseyData::create(array_merge([
            'tanggal_masuk' => '2026-10-20',
            'desainer' => 'AAN',
            'customer' => 'ARBI PAIJO - 1810',
            'deadline' => '2026-11-05',
            'jenis_kerah' => 'O-Neck',
            'jenis_bahan' => 'Milano',
            'model' => 'Pendek',
            'status' => JerseyData::STATUS_PROSES_DESAIN,
            'catatan' => 'Menunggu DP',
        ], $overrides));
    }

    public function test_guest_is_redirected_and_produksi_is_forbidden(): void
    {
        $this->seedMaster();
        $entry = $this->entry();

        $this->get(route('admin.jersey.index'))->assertRedirect(route('login'));
        $this->get(route('admin.jersey.create'))->assertRedirect(route('login'));

        $this->actingAs($this->produksi())->get(route('admin.jersey.index'))->assertForbidden();
        $this->actingAs($this->produksi())->get(route('admin.jersey.edit', $entry))->assertForbidden();
    }

    public function test_index_page_shows_toolbar_columns_and_empty_state(): void
    {
        $this->seedMaster();

        $this->actingAs($this->admin())
            ->get(route('admin.jersey.index'))
            ->assertOk()
            ->assertSee('Data Jersey')
            ->assertSee('Belum ada data jersey', false);
    }

    public function test_jersey_entry_can_be_created_edited_and_deleted(): void
    {
        $this->seedMaster();

        $this->actingAs($this->admin())
            ->post(route('admin.jersey.store'), [
                'tanggal_masuk' => '2026-10-20',
                'desainer' => 'AAN',
                'customer' => 'ARBI PAIJO - 1810',
                'deadline' => '2026-11-05',
                'jenis_kerah' => 'O-Neck',
                'jenis_bahan' => 'Milano',
                'model' => 'Pendek',
                'status' => 'Layout',
                'catatan' => 'Prioritas',
            ])
            ->assertRedirect(route('admin.jersey.index'));

        $entry = JerseyData::firstOrFail();
        $this->assertSame('Layout', $entry->status);
        $this->assertSame('AAN', $entry->desainer);

        $this->actingAs($this->admin())
            ->put(route('admin.jersey.update', $entry), [
                'tanggal_masuk' => '2026-10-20',
                'desainer' => 'ARYO',
                'customer' => 'ARBI PAIJO - 1810',
                'deadline' => null,
                'jenis_kerah' => 'O-Neck',
                'jenis_bahan' => 'Milano',
                'model' => 'Pendek',
                'status' => 'Done',
                'catatan' => null,
            ])
            ->assertRedirect(route('admin.jersey.index'));

        $this->assertSame('Done', $entry->fresh()->status);
        $this->assertSame('ARYO', $entry->fresh()->desainer);

        $this->actingAs($this->admin())
            ->get(route('admin.jersey.index'))
            ->assertOk()
            ->assertSee('ARBI PAIJO - 1810');

        $this->actingAs($this->admin())
            ->delete(route('admin.jersey.destroy', $entry))
            ->assertRedirect(route('admin.jersey.index'));

        $this->assertDatabaseCount('jersey_data', 0);
    }

    public function test_store_validates_desainer_status_and_deadline(): void
    {
        $this->seedMaster();

        $this->actingAs($this->admin())
            ->from(route('admin.jersey.index'))
            ->post(route('admin.jersey.store'), [
                'tanggal_masuk' => '2026-10-20',
                'desainer' => 'TIDAK ADA',
                'customer' => '',
                'status' => 'Bogus',
                'deadline' => '2026-10-01',
            ])
            ->assertRedirect(route('admin.jersey.index'))
            ->assertSessionHasErrors(['desainer', 'customer', 'status', 'deadline']);
    }

    public function test_index_search_filter_and_sort(): void
    {
        $this->seedMaster();
        $this->entry(['customer' => 'BUDI - 99', 'desainer' => 'ARYO', 'deadline' => '2026-01-01', 'status' => 'Done']);
        $this->entry(['customer' => 'CEDEK - 1', 'desainer' => 'AAN', 'deadline' => '2026-12-31']);

        $response = $this->actingAs($this->admin())->get(route('admin.jersey.index', ['q' => 'BUDI']));
        $response->assertOk()->assertSee('BUDI - 99')->assertDontSee('CEDEK - 1');

        $this->actingAs($this->admin())
            ->get(route('admin.jersey.index', ['desainer' => 'ARYO']))
            ->assertOk()
            ->assertSee('BUDI - 99')
            ->assertDontSee('CEDEK - 1');

        $this->actingAs($this->admin())
            ->get(route('admin.jersey.index', ['status' => 'Done']))
            ->assertOk()
            ->assertSee('BUDI - 99')
            ->assertDontSee('CEDEK - 1');

        $html = $this->actingAs($this->admin())
            ->get(route('admin.jersey.index', ['sort' => 'customer:asc']))
            ->assertOk()
            ->getContent();

        $this->assertLessThan(strpos($html, 'CEDEK - 1'), strpos($html, 'BUDI - 99'));
    }

    public function test_sisa_hari_computes_remaining_days_and_late_flag(): void
    {
        $this->seedMaster();

        $ontime = $this->entry(['deadline' => now()->addDays(3)->toDateString()]);
        $late = $this->entry(['customer' => 'LATE - 1', 'deadline' => now()->subDay()->toDateString()]);
        $none = $this->entry(['customer' => 'NONE - 1', 'deadline' => null]);

        $this->assertSame(3, $ontime->sisa_hari);
        $this->assertFalse($ontime->terlambat);

        $this->assertSame(-1, $late->sisa_hari);
        $this->assertTrue($late->terlambat);

        $this->assertNull($none->sisa_hari);
        $this->assertFalse($none->terlambat);

        $this->actingAs($this->admin())
            ->get(route('admin.jersey.index'))
            ->assertOk()
            ->assertSee('TERLAMBAT');
    }

    public function test_form_shows_master_dropdown_options(): void
    {
        $this->seedMaster();

        $this->actingAs($this->admin())
            ->get(route('admin.jersey.create'))
            ->assertOk()
            ->assertSee('AAN')
            ->assertSee('ARYO')
            ->assertSee('O-Neck')
            ->assertSee('Milano')
            ->assertSee('Pendek')
            ->assertSee('Printing');
    }
}
