<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductGalleryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function fakeImage(string $name = 'galeri.png'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'bcgal');

        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    private function product(): Product
    {
        $category = Category::firstOrCreate(['slug' => 'jersey'], ['name' => 'Jersey']);

        return Product::create([
            'category_id' => $category->id,
            'title' => 'Jersey Galeri Tes',
            'slug' => 'jersey-galeri-tes',
        ]);
    }

    public function test_product_store_saves_multiple_gallery_images(): void
    {
        Storage::fake('public');
        $category = Category::firstOrCreate(['slug' => 'sablon'], ['name' => 'Sablon']);

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'title' => 'Kaos Galeri',
            'category_id' => $category->id,
            'images' => [$this->fakeImage('satu.png'), $this->fakeImage('dua.png')],
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('slug', 'kaos-galeri')->firstOrFail();
        $this->assertCount(2, $product->images);
        $this->assertSame([0, 1], $product->images->pluck('sort')->all());
        $this->assertSame('Kaos Galeri', $product->images->first()->alt);

        foreach ($product->images as $galleryImage) {
            Storage::disk('public')->assertExists($galleryImage->image);
        }
    }

    public function test_product_update_appends_new_and_removes_checked_gallery_images(): void
    {
        Storage::fake('public');
        $product = $this->product();

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'title' => $product->title,
            'category_id' => $product->category_id,
            'images' => [$this->fakeImage('a.png'), $this->fakeImage('b.png')],
        ])->assertRedirect(route('admin.products.index'));

        $first = $product->images()->orderBy('sort')->firstOrFail();
        $firstPath = $first->image;
        Storage::disk('public')->assertExists($firstPath);

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'title' => $product->title,
            'category_id' => $product->category_id,
            'images' => [$this->fakeImage('c.png')],
            'remove_images' => [$first->id],
        ])->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertCount(2, $product->images);
        $this->assertNull($product->images()->find($first->id));
        Storage::disk('public')->assertMissing($firstPath);
        $this->assertSame(2, (int) $product->images()->max('sort'));

        // Gambar utama & data inti tetap utuh.
        $this->assertSame($product->title, $product->fresh()->title);
    }

    public function test_gallery_upload_validates_count_and_file_type(): void
    {
        Storage::fake('public');
        $product = $this->product();

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'title' => $product->title,
            'category_id' => $product->category_id,
            'images' => [
                $this->fakeImage('1.png'),
                $this->fakeImage('2.png'),
                $this->fakeImage('3.png'),
                $this->fakeImage('4.png'),
                $this->fakeImage('5.png'),
                $this->fakeImage('6.png'),
            ],
        ])->assertSessionHasErrors('images');

        $invalid = tempnam(sys_get_temp_dir(), 'bcgal');
        file_put_contents($invalid, 'bukan gambar');

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'title' => $product->title,
            'category_id' => $product->category_id,
            'images' => [new UploadedFile($invalid, 'catatan.txt', 'text/plain', null, true)],
        ])->assertSessionHasErrors('images.0');

        $this->assertSame(0, $product->images()->count());
    }

    public function test_remove_images_must_belong_to_the_product(): void
    {
        Storage::fake('public');
        $product = $this->product();

        $other = Product::create([
            'category_id' => $product->category_id,
            'title' => 'Produk Lain',
            'slug' => 'produk-lain-galeri',
        ]);
        $otherImage = $other->images()->create(['image' => 'products/milik-orang.png', 'sort' => 0]);

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'title' => $product->title,
            'category_id' => $product->category_id,
            'remove_images' => [$otherImage->id],
        ])->assertSessionHasErrors('remove_images.0');

        $this->assertNotNull($other->images()->find($otherImage->id));
    }

    public function test_product_destroy_removes_gallery_files_and_rows(): void
    {
        Storage::fake('public');
        $product = $this->product();

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'title' => $product->title,
            'category_id' => $product->category_id,
            'images' => [$this->fakeImage('hapus1.png'), $this->fakeImage('hapus2.png')],
        ])->assertRedirect(route('admin.products.index'));

        $paths = $product->images()->pluck('image')->all();
        $this->assertCount(2, $paths);

        $this->actingAs($this->admin())->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertSame(0, Product::where('id', $product->id)->count());
        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_product_detail_shows_gallery_carousel(): void
    {
        $product = $this->product();
        $product->images()->create(['image' => 'products/galeri-1.jpg', 'sort' => 0, 'alt' => 'Foto 1']);
        $product->images()->create(['image' => 'products/galeri-2.jpg', 'sort' => 1, 'alt' => 'Foto 2']);

        $this->get(route('produk.show', $product->slug))
            ->assertOk()
            ->assertSee('x-data="imageSlider(', false)
            ->assertSee('aria-label="Foto sebelumnya"', false)
            ->assertSee('aria-label="Foto berikutnya"', false)
            ->assertSee('storage/products/galeri-1.jpg', false)
            ->assertSee('storage/products/galeri-2.jpg', false)
            ->assertSee('Foto 1');
    }

    public function test_product_detail_without_gallery_keeps_single_image_layout(): void
    {
        $product = $this->product();

        $this->get(route('produk.show', $product->slug))
            ->assertOk()
            ->assertSee('Foto Produk')
            ->assertDontSee('Foto berikutnya', false)
            ->assertDontSee('x-data="imageSlider(', false);
    }

    public function test_product_detail_json_ld_includes_gallery_images(): void
    {
        $product = $this->product();
        $product->images()->create(['image' => 'products/jsonld-1.jpg', 'sort' => 0]);

        $this->get(route('produk.show', $product->slug))
            ->assertOk()
            ->assertSee('"@type":"Product"', false)
            ->assertSee(asset('storage/products/jsonld-1.jpg'), false);
    }
}
