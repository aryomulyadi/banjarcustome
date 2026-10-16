<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_calculator(): void
    {
        $this->get(route('admin.calculator'))->assertRedirect(route('login'));
    }

    public function test_non_admin_gets_403_on_calculator(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('admin.calculator'))->assertForbidden();
    }

    public function test_admin_sees_calculator_with_product_price_data(): void
    {
        $category = Category::create(['name' => 'Kaos', 'slug' => 'kaos-kalk']);

        Product::create([
            'category_id' => $category->id,
            'title' => 'Kaos Polos Hitam',
            'slug' => 'kaos-polos-hitam-kalk',
            'base_price' => 45000,
            'price_tiers' => [
                ['min_qty' => 50, 'unit_price' => 40000],
            ],
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.calculator'))
            ->assertOk()
            ->assertSee('Kalkulator Harga')
            ->assertSee('Kaos Polos Hitam')
            ->assertSee('basePrice');
    }

    public function test_product_form_persists_base_price_and_tiers(): void
    {
        $category = Category::create(['name' => 'Kaos', 'slug' => 'kaos-form-harga']);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'title' => 'Kaos Dengan Harga',
                'category_id' => $category->id,
                'base_price' => 42000,
                'price_tiers' => [
                    ['min_qty' => 50, 'unit_price' => 40000],
                    ['min_qty' => 100, 'unit_price' => 38000],
                ],
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::where('title', 'Kaos Dengan Harga')->firstOrFail();

        $this->assertSame(42000, $product->base_price);
        $this->assertSame(50, $product->price_tiers[0]['min_qty']);
        $this->assertSame(38000, $product->price_tiers[1]['unit_price']);
    }

    public function test_product_form_rejects_invalid_tiers(): void
    {
        $category = Category::create(['name' => 'Kaos', 'slug' => 'kaos-form-tolak']);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'title' => 'Kaos Salah Harga',
                'category_id' => $category->id,
                'price_tiers' => [
                    ['min_qty' => 0, 'unit_price' => -5],
                ],
            ])
            ->assertSessionHasErrors(['price_tiers.0.min_qty', 'price_tiers.0.unit_price']);
    }

    public function test_product_form_rejects_duplicate_min_qty(): void
    {
        $category = Category::create(['name' => 'Kaos', 'slug' => 'kaos-form-ganda']);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'title' => 'Kaos Tangga Ganda',
                'category_id' => $category->id,
                'base_price' => 45000,
                'price_tiers' => [
                    ['min_qty' => 50, 'unit_price' => 40000],
                    ['min_qty' => 50, 'unit_price' => 39000],
                ],
            ])
            ->assertSessionHasErrors('price_tiers');

        $this->assertDatabaseCount('products', 0);
    }
}
