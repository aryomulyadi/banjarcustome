<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Gallery;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function fakeImage(string $name = 'gambar.png'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'bcimg');

        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    private function category(): Category
    {
        return Category::firstOrCreate(['slug' => 'sablon'], ['name' => 'Sablon']);
    }

    public function test_product_pages_return_successful_response(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.products.index'))->assertOk()->assertSee('Produk');
        $this->actingAs($admin)->get(route('admin.products.create'))->assertOk()->assertSee('Tambah Produk');
    }

    public function test_product_can_be_created_with_image_and_colors(): void
    {
        Storage::fake('public');

        $category = $this->category();

        $response = $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'title' => 'Kaos Promosi Custom',
            'category_id' => $category->id,
            'description' => 'Kaos promosi sablon 1 warna.',
            'price_estimate' => 'Rp40.000 / pcs',
            'image' => $this->fakeImage(),
            'colors' => [
                ['name' => 'Navy', 'hex' => '#1e3a8a'],
                ['name' => 'Maroon', 'hex' => '#7f1d1d'],
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('slug', 'kaos-promosi-custom')->first();

        $this->assertNotNull($product);
        $this->assertNotNull($product->image);
        $this->assertStringStartsWith('products/', $product->image);
        Storage::disk('public')->assertExists($product->image);
        $this->assertCount(2, $product->colors);
        $this->assertDatabaseHas('product_colors', ['product_id' => $product->id, 'name' => 'Navy', 'hex' => '#1e3a8a']);
    }

    public function test_product_requires_title_and_category(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'title' => '',
                'category_id' => 9999,
            ])
            ->assertSessionHasErrors(['title', 'category_id']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_update_replaces_image_and_colors(): void
    {
        Storage::fake('public');

        $category = $this->category();
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Produk Lama',
            'slug' => 'produk-lama',
            'image' => 'products/lama.png',
        ]);
        $product->colors()->create(['name' => 'Merah', 'hex' => '#ff0000']);
        Storage::disk('public')->put('products/lama.png', 'isi-lama');

        $response = $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'title' => 'Produk Terbaru',
            'category_id' => $category->id,
            'description' => 'Deskripsi diperbarui.',
            'image' => $this->fakeImage('baru.png'),
            'colors' => [
                ['name' => 'Biru', 'hex' => '#0000ff'],
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product->refresh();

        $this->assertSame('Produk Terbaru', $product->title);
        $this->assertSame('produk-lama', $product->slug);
        Storage::disk('public')->assertMissing('products/lama.png');
        Storage::disk('public')->assertExists($product->image);
        $this->assertCount(1, $product->colors);
        $this->assertDatabaseHas('product_colors', ['product_id' => $product->id, 'name' => 'Biru']);
    }

    public function test_product_update_keeps_image_when_no_new_upload(): void
    {
        Storage::fake('public');

        $category = $this->category();
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Produk Tetap',
            'slug' => 'produk-tetap',
            'image' => 'products/tetap.png',
        ]);
        Storage::disk('public')->put('products/tetap.png', 'isi');

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'title' => 'Produk Tetap Diubah',
            'category_id' => $category->id,
        ])->assertRedirect(route('admin.products.index'));

        $this->assertSame('products/tetap.png', $product->fresh()->image);
        Storage::disk('public')->assertExists('products/tetap.png');
    }

    public function test_product_can_be_deleted_with_its_colors_and_image(): void
    {
        Storage::fake('public');

        $category = $this->category();
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Produk Hapus',
            'slug' => 'produk-hapus',
            'image' => 'products/hapus.png',
        ]);
        $product->colors()->create(['name' => 'Hitam', 'hex' => '#000000']);
        Storage::disk('public')->put('products/hapus.png', 'isi');

        $this->actingAs($this->admin())
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseCount('product_colors', 0);
        Storage::disk('public')->assertMissing('products/hapus.png');
    }

    public function test_category_can_be_created_and_deleted(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), ['name' => 'Kemeja'])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::where('slug', 'kemeja')->first();
        $this->assertNotNull($category);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = $this->category();
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Kaos Sablon',
            'slug' => 'kaos-sablon',
        ]);

        $this->actingAs($this->admin())
            ->from(route('admin.categories.index'))
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_gallery_can_be_created_updated_and_deleted(): void
    {
        Storage::fake('public');

        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.galleries.store'), [
            'title' => 'Jersey Futsal Garuda',
            'caption' => 'Hasil produksi jersey printing.',
            'category' => 'Jersey',
            'image' => $this->fakeImage(),
        ])->assertRedirect(route('admin.galleries.index'));

        $gallery = Gallery::where('title', 'Jersey Futsal Garuda')->first();
        $this->assertNotNull($gallery);
        Storage::disk('public')->assertExists($gallery->image);

        $this->actingAs($admin)->put(route('admin.galleries.update', $gallery), [
            'title' => 'Jersey Futsal Garuda Updated',
            'image' => $this->fakeImage('galeri-baru.png'),
        ])->assertRedirect(route('admin.galleries.index'));

        Storage::disk('public')->assertMissing($gallery->image);
        $gallery->refresh();
        $this->assertSame('Jersey Futsal Garuda Updated', $gallery->title);
        Storage::disk('public')->assertExists($gallery->image);

        $this->actingAs($admin)->delete(route('admin.galleries.destroy', $gallery))
            ->assertRedirect(route('admin.galleries.index'));

        $this->assertDatabaseMissing('galleries', ['id' => $gallery->id]);
        Storage::disk('public')->assertMissing($gallery->image);
    }

    public function test_gallery_validation_requires_title(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.galleries.create'))
            ->post(route('admin.galleries.store'), ['title' => ''])
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('galleries', 0);
    }
}
