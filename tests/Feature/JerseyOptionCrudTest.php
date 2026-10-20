<?php

namespace Tests\Feature;

use App\Models\MasterOption;
use App\Models\User;
use App\Support\ImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JerseyOptionCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function fakeImage(string $name = 'foto.png'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'bcimg');

        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    public function test_master_index_shows_jersey_option_sections(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.master.index'))
            ->assertOk()
            ->assertSee('Kerah Jersey')
            ->assertSee('Bahan Jersey')
            ->assertSee('Foto opsi (opsional, maks 2 MB)');
    }

    public function test_jersey_collar_can_be_created_with_fee_and_photo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('admin.master.store'), [
                'kind' => MasterOption::KIND_COLLAR,
                'name' => 'Wangky Rantai',
                'fee' => 9000,
                'image' => $this->fakeImage(),
            ])
            ->assertRedirect(route('admin.master.index'));

        $option = MasterOption::query()
            ->where('kind', MasterOption::KIND_COLLAR)
            ->where('name', 'Wangky Rantai')
            ->firstOrFail();

        $this->assertSame(9000, $option->fee);
        $this->assertTrue($option->active);
        $this->assertStringStartsWith('jersey-options/', $option->image);
        $this->assertTrue(Storage::disk('public')->exists($option->image));
        $this->assertTrue(Storage::disk('public')->exists(ImageOptimizer::webpPath($option->image)));
    }

    public function test_store_validates_fee_and_photo_for_jersey_kinds(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.master.store'), [
                'kind' => MasterOption::KIND_COLLAR,
                'name' => 'Kerah Baru',
                'fee' => 'gratis',
            ])
            ->assertSessionHasErrors('fee');

        $invalid = tempnam(sys_get_temp_dir(), 'bcimg');
        file_put_contents($invalid, 'bukan gambar');

        $this->actingAs($admin)
            ->post(route('admin.master.store'), [
                'kind' => MasterOption::KIND_FABRIC,
                'name' => 'Bahan Baru',
                'fee' => 5000,
                'image' => new UploadedFile($invalid, 'bukti.txt', 'text/plain', null, true),
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('options', 0);
    }

    public function test_jersey_option_can_be_updated_with_new_photo_fee_and_deactivated(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('jersey-options/old.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        $option = MasterOption::create([
            'kind' => MasterOption::KIND_FABRIC,
            'name' => 'Embos Lama',
            'fee' => 10000,
            'image' => 'jersey-options/old.png',
            'active' => true,
            'sort' => 0,
        ]);

        $this->actingAs($this->admin())
            ->patch(route('admin.master.update', $option), [
                'name' => 'Emos Pro',
                'fee' => 12000,
                'sort' => 3,
                'image' => $this->fakeImage('baru.png'),
            ])
            ->assertRedirect(route('admin.master.index'));

        $option->refresh();

        $this->assertSame('Emos Pro', $option->name);
        $this->assertSame(12000, $option->fee);
        $this->assertSame(3, $option->sort);
        $this->assertFalse($option->active);
        $this->assertStringStartsWith('jersey-options/', $option->image);
        Storage::disk('public')->assertMissing('jersey-options/old.png');
        $this->assertTrue(Storage::disk('public')->exists($option->image));
    }

    public function test_jersey_option_update_keeps_photo_when_not_replaced(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('jersey-options/tetap.png', 'isi');

        $option = MasterOption::create([
            'kind' => MasterOption::KIND_COLLAR,
            'name' => 'O-Neck Variasi',
            'fee' => 8000,
            'image' => 'jersey-options/tetap.png',
            'active' => false,
            'sort' => 1,
        ]);

        $this->actingAs($this->admin())
            ->patch(route('admin.master.update', $option), [
                'name' => 'O-Neck Variasi',
                'fee' => 8500,
                'active' => '1',
                'sort' => 1,
            ])
            ->assertRedirect(route('admin.master.index'));

        $option->refresh();

        $this->assertSame(8500, $option->fee);
        $this->assertTrue($option->active);
        $this->assertSame('jersey-options/tetap.png', $option->image);
        $this->assertTrue(Storage::disk('public')->exists('jersey-options/tetap.png'));
    }

    public function test_destroy_removes_photo_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('jersey-options/hapus.png', 'isi');
        Storage::disk('public')->put('jersey-options/hapus.webp', 'isi');

        $option = MasterOption::create([
            'kind' => MasterOption::KIND_COLLAR,
            'name' => 'Dihapus',
            'fee' => 0,
            'image' => 'jersey-options/hapus.png',
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.master.destroy', $option))
            ->assertRedirect(route('admin.master.index'));

        $this->assertDatabaseMissing('options', ['id' => $option->id]);
        Storage::disk('public')->assertMissing('jersey-options/hapus.png');
        Storage::disk('public')->assertMissing('jersey-options/hapus.webp');
    }

    public function test_edit_and_update_reject_non_jersey_kinds(): void
    {
        $option = MasterOption::create(['kind' => MasterOption::KIND_TYPE, 'name' => 'Kaos']);

        $this->actingAs($this->admin())
            ->get(route('admin.master.edit', $option))
            ->assertNotFound();

        $this->actingAs($this->admin())
            ->patch(route('admin.master.update', $option), ['name' => 'Kaos Baru', 'fee' => 1000])
            ->assertNotFound();
    }

    public function test_edit_page_rejects_guests_and_non_admins(): void
    {
        $option = MasterOption::create([
            'kind' => MasterOption::KIND_COLLAR,
            'name' => 'O-Neck',
        ]);

        $this->get(route('admin.master.edit', $option))
            ->assertRedirect(route('login'));

        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.master.edit', $option))
            ->assertForbidden();
    }
}
