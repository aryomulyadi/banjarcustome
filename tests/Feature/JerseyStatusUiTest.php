<?php

namespace Tests\Feature;

use App\Models\JerseyData;
use App\Models\MasterOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JerseyStatusUiTest extends TestCase
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
        MasterOption::create(['kind' => MasterOption::KIND_DESIGNER, 'name' => 'AAN']);
    }

    public function test_status_endpoints_auth_protection(): void
    {
        $this->seedMaster();
        $entry = JerseyData::create([
            'tanggal_masuk' => '2026-10-20',
            'desainer' => 'AAN',
            'customer' => 'Test Guest',
            'status' => 'Proses Desain',
        ]);

        $this->patch(route('admin.jersey.status', $entry), ['status' => 'Done'])
            ->assertRedirect(route('login'));

        $this->patch(route('admin.jersey.bulk-status'), ['ids' => [$entry->id], 'status' => 'Done'])
            ->assertRedirect(route('login'));

        $this->actingAs($this->produksi())
            ->patch(route('admin.jersey.status', $entry), ['status' => 'Done'])
            ->assertForbidden();
    }

    public function test_multi_status_filter_and_sticky_header(): void
    {
        $this->seedMaster();
        JerseyData::create(['tanggal_masuk' => '2026-10-20', 'desainer' => 'AAN', 'customer' => 'Cust 1', 'status' => 'Done']);
        JerseyData::create(['tanggal_masuk' => '2026-10-21', 'desainer' => 'AAN', 'customer' => 'Cust 2', 'status' => 'Layout']);
        JerseyData::create(['tanggal_masuk' => '2026-10-22', 'desainer' => 'AAN', 'customer' => 'Cust 3', 'status' => 'Jahit']);

        $res = $this->actingAs($this->admin())
            ->get(route('admin.jersey.index', ['status' => ['Done', 'Layout']]));

        $res->assertOk()
            ->assertSee('Cust 1')
            ->assertSee('Cust 2')
            ->assertDontSee('Cust 3')
            ->assertSee('sticky top-0', false)
            ->assertSee('max-h-[70vh]', false);
    }

    public function test_bulk_status_update(): void
    {
        $this->seedMaster();
        $e1 = JerseyData::create(['tanggal_masuk' => '2026-10-20', 'desainer' => 'AAN', 'customer' => 'C1', 'status' => 'Proses Desain']);
        $e2 = JerseyData::create(['tanggal_masuk' => '2026-10-20', 'desainer' => 'AAN', 'customer' => 'C2', 'status' => 'Proses Desain']);

        $res = $this->actingAs($this->admin())
            ->patch(route('admin.jersey.bulk-status'), [
                'ids' => [$e1->id, $e2->id],
                'status' => 'Done',
            ]);

        $res->assertRedirect();
        $this->assertSame('Done', $e1->fresh()->status);
        $this->assertSame('Done', $e2->fresh()->status);
    }

    public function test_quick_edit_status_json_patch(): void
    {
        $this->seedMaster();
        $entry = JerseyData::create(['tanggal_masuk' => '2026-10-20', 'desainer' => 'AAN', 'customer' => 'C Quick', 'status' => 'Proses Desain']);

        $res = $this->actingAs($this->admin())
            ->patchJson(route('admin.jersey.status', $entry), ['status' => 'Printing']);

        $res->assertOk()
            ->assertJson(['status' => 'Printing']);

        $this->assertSame('Printing', $entry->fresh()->status);
    }

    public function test_row_tint_classes_rendered(): void
    {
        $this->seedMaster();
        JerseyData::create([
            'tanggal_masuk' => '2026-01-01',
            'deadline' => '2026-01-02',
            'desainer' => 'AAN',
            'customer' => 'Late Cust',
            'status' => 'Proses Desain',
        ]);
        JerseyData::create([
            'tanggal_masuk' => '2026-10-20',
            'desainer' => 'AAN',
            'customer' => 'Cancel Cust',
            'status' => 'Cancel',
        ]);

        $res = $this->actingAs($this->admin())->get(route('admin.jersey.index'));

        $res->assertOk()
            ->assertSee('border-l-destructive', false)
            ->assertSee('bg-secondary/40', false);
    }
}
