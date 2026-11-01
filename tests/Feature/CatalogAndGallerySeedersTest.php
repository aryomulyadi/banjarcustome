<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Gallery;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\GallerySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogAndGallerySeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_seeder_populates_categories_and_products(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->assertGreaterThan(0, Category::count());
        $this->assertGreaterThan(0, Product::count());

        $this->assertDatabaseHas('categories', ['slug' => 'kaos']);
        $this->assertDatabaseHas('products', ['slug' => 'kemeja-kantor-seragam']);
    }

    public function test_gallery_seeder_populates_items_with_images(): void
    {
        $this->seed(GallerySeeder::class);

        $this->assertSame(8, Gallery::count());
        $this->assertSame(0, Gallery::whereNull('image')->count());

        $first = Gallery::first();
        $this->assertNotNull($first->image);
        $this->assertStringStartsWith('galleries/', $first->image);
    }

    public function test_gallery_seeder_is_idempotent(): void
    {
        $this->seed(GallerySeeder::class);
        $countFirst = Gallery::count();

        $this->seed(GallerySeeder::class);
        $this->assertSame($countFirst, Gallery::count());
    }

    public function test_admin_can_trigger_catalog_sync_via_post_endpoint(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.products.sync'));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertGreaterThan(0, Category::count());
        $this->assertGreaterThan(0, Product::count());
    }
}
