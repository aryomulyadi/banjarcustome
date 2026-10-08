<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomOrder;
use App\Models\Faq;
use App\Models\Product;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\ImageOptimizer;
use Database\Seeders\FaqSeeder;
use Database\Seeders\TestimonialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function order(array $attributes = []): CustomOrder
    {
        $category = Category::firstOrCreate(['slug' => 'sablon'], ['name' => 'Sablon']);
        $product = Product::firstOrCreate(['slug' => 'kaos-tes'], [
            'category_id' => $category->id,
            'title' => 'Kaos Tes',
        ]);

        return CustomOrder::create(array_merge([
            'product_id' => $product->id,
            'name' => 'Pelanggan Konten',
            'whatsapp_number' => '081122233344',
            'order_details' => 'Detail pesanan untuk pengujian konten admin.',
        ], $attributes));
    }

    public function test_faq_can_be_created_updated_and_deleted(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.faqs.store'), [
            'question' => 'Bisa eceran?',
            'answer' => 'Bisa, minimal 1 pcs.',
            'position' => 1,
            'is_active' => '1',
        ])->assertRedirect(route('admin.faqs.index'));

        $faq = Faq::where('question', 'Bisa eceran?')->first();
        $this->assertNotNull($faq);
        $this->assertTrue($faq->is_active);

        $this->actingAs($admin)->put(route('admin.faqs.update', $faq), [
            'question' => 'Bisa pesan eceran?',
            'answer' => 'Bisa, minimal 1 pcs dengan harga satuan.',
            'position' => 2,
        ])->assertRedirect(route('admin.faqs.index'));

        $faq->refresh();
        $this->assertSame('Bisa pesan eceran?', $faq->question);
        $this->assertFalse($faq->is_active);

        $this->actingAs($admin)->delete(route('admin.faqs.destroy', $faq))
            ->assertRedirect(route('admin.faqs.index'));

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_faq_requires_question_and_answer(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.faqs.create'))
            ->post(route('admin.faqs.store'), ['question' => '', 'answer' => ''])
            ->assertSessionHasErrors(['question', 'answer']);

        $this->assertDatabaseCount('faqs', 0);
    }

    public function test_admin_content_pages_render_successfully(): void
    {
        $admin = $this->admin();
        $faq = Faq::create(['question' => 'Pertanyaan?', 'answer' => 'Jawaban.', 'position' => 1, 'is_active' => true]);
        $testimonial = Testimonial::create(['name' => 'Warga', 'rating' => 5, 'content' => 'Mantap.', 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk()
            ->assertSee('Jenis Sablon & Teknik')
            ->assertSee('Sablon Plastisol')
            ->assertSee('services: JSON.parse(\'[{\u0022title\u0022', false)
            ->assertSee('Sablon Plastisol', false);
        $this->actingAs($admin)->get(route('admin.faqs.create'))->assertOk()->assertSee('Pertanyaan');
        $this->actingAs($admin)->get(route('admin.faqs.edit', $faq))->assertOk()->assertSee('Pertanyaan?');
        $this->actingAs($admin)->get(route('admin.testimonials.create'))->assertOk()->assertSee('Testimoni');
        $this->actingAs($admin)->get(route('admin.testimonials.edit', $testimonial))->assertOk()->assertSee('Warga');
        $this->actingAs($admin)->get(route('admin.profile.edit'))->assertOk()->assertSee('Password');
    }

    public function test_testimonial_can_be_created_updated_and_deleted(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'name' => 'Andi',
            'role' => 'Karyawan',
            'city' => 'Banjarmasin',
            'rating' => 5,
            'content' => 'Hasil sablon rapi dan pengiriman cepat.',
            'is_active' => '1',
        ])->assertRedirect(route('admin.testimonials.index'));

        $testimonial = Testimonial::where('name', 'Andi')->first();
        $this->assertNotNull($testimonial);

        $this->actingAs($admin)->put(route('admin.testimonials.update', $testimonial), [
            'name' => 'Andi Pratama',
            'rating' => 4,
            'content' => 'Hasil sablon bagus, admin ramah.',
        ])->assertRedirect(route('admin.testimonials.index'));

        $testimonial->refresh();
        $this->assertSame('Andi Pratama', $testimonial->name);
        $this->assertSame(4, $testimonial->rating);
        $this->assertFalse($testimonial->is_active);

        $this->actingAs($admin)->delete(route('admin.testimonials.destroy', $testimonial))
            ->assertRedirect(route('admin.testimonials.index'));

        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }

    public function test_testimonial_photo_upload_creates_webp_and_renders_on_home(): void
    {
        Storage::fake('public');

        $admin = $this->admin();

        $path = tempnam(sys_get_temp_dir(), 'bcimg');
        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'name' => 'Budi Santoso',
            'rating' => 5,
            'content' => 'Hasil sablon rapi sekali.',
            'is_active' => '1',
            'photo' => new UploadedFile($path, 'foto.png', 'image/png', null, true),
        ])->assertRedirect(route('admin.testimonials.index'));

        $testimonial = Testimonial::where('name', 'Budi Santoso')->firstOrFail();

        $this->assertStringStartsWith('testimonials/', $testimonial->photo);
        Storage::disk('public')->assertExists($testimonial->photo);
        Storage::disk('public')->assertExists(ImageOptimizer::webpPath($testimonial->photo));

        $this->get('/')
            ->assertOk()
            ->assertSee('type="image/webp"', false)
            ->assertSee('Budi Santoso');
    }

    public function test_active_testimonial_shows_on_home_page(): void
    {
        Testimonial::create([
            'name' => 'Pelanggan Puas',
            'rating' => 5,
            'content' => 'Sablon awet, warna tidak pudar.',
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Sablon awet, warna tidak pudar.')
            ->assertSee('Pelanggan Puas');
    }

    public function test_home_content_settings_can_be_updated(): void
    {
        $admin = $this->admin();

        $payload = [
            'slides' => [
                ['title' => 'Promo Baru Satu', 'subtitle' => 'Subtitle satu', 'image' => null],
                ['title' => 'Promo Baru Dua', 'subtitle' => 'Subtitle dua', 'image' => null],
                ['title' => 'Promo Baru Tiga', 'subtitle' => 'Subtitle tiga', 'image' => null],
            ],
            'stats' => [
                ['value' => '999+', 'label' => 'Pesanan Uji'],
                ['value' => '50+', 'label' => 'Klien Uji'],
                ['value' => '10+', 'label' => 'Tahun Uji'],
            ],
            'services' => [
                ['title' => 'Sablon Uji Satu', 'desc' => 'Deskripsi layanan uji satu.'],
                ['title' => 'Sablon Uji Dua', 'desc' => ''],
            ],
        ];

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $payload)
            ->assertRedirect(route('admin.settings.edit'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Promo Baru Satu')
            ->assertSee('Pesanan Uji');

        $this->get('/layanan')
            ->assertOk()
            ->assertSee('Sablon Uji Satu')
            ->assertDontSee('Sablon Plastisol');

        $this->get('/pesan')
            ->assertOk()
            ->assertSee('Sablon Uji Satu')
            ->assertDontSee('Sablon Plastisol');
    }

    public function test_home_content_settings_require_exactly_three_slides(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), [
                'slides' => [
                    ['title' => 'Satu', 'subtitle' => 'Sub satu', 'image' => null],
                ],
                'stats' => [
                    ['value' => '1', 'label' => 'Satu'],
                ],
                'services' => [
                    ['title' => 'Satu Layanan', 'desc' => ''],
                ],
            ])
            ->assertSessionHasErrors(['slides', 'stats']);
    }

    public function test_settings_require_at_least_one_service(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), [
                'slides' => [
                    ['title' => 'Satu', 'subtitle' => 'Sub satu', 'image' => null],
                    ['title' => 'Dua', 'subtitle' => 'Sub dua', 'image' => null],
                    ['title' => 'Tiga', 'subtitle' => 'Sub tiga', 'image' => null],
                ],
                'stats' => [
                    ['value' => '1', 'label' => 'Satu'],
                    ['value' => '2', 'label' => 'Dua'],
                    ['value' => '3', 'label' => 'Tiga'],
                ],
                'services' => [],
            ])
            ->assertSessionHasErrors('services');

        $this->actingAs($this->admin())
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), [
                'slides' => [
                    ['title' => 'Satu', 'subtitle' => 'Sub satu', 'image' => null],
                    ['title' => 'Dua', 'subtitle' => 'Sub dua', 'image' => null],
                    ['title' => 'Tiga', 'subtitle' => 'Sub tiga', 'image' => null],
                ],
                'stats' => [
                    ['value' => '1', 'label' => 'Satu'],
                    ['value' => '2', 'label' => 'Dua'],
                    ['value' => '3', 'label' => 'Tiga'],
                ],
                'services' => [
                    ['title' => str_repeat('x', 61), 'desc' => ''],
                ],
            ])
            ->assertSessionHasErrors('services.0.title');
    }

    public function test_seeded_faqs_are_shown_on_public_faq_page(): void
    {
        $this->seed(FaqSeeder::class);

        $response = $this->get('/faq');

        $response->assertOk()
            ->assertSee('Berapa lama proses produksinya?')
            ->assertSee('Bagaimana skema pembayarannya?')
            ->assertSee('Apa saja jenis sablon yang tersedia?')
            ->assertSee('Bagaimana cara cek status pesanan?');

        $this->assertSame(10, Faq::active()->count());
    }

    public function test_seeded_google_reviews_show_on_home_with_badge(): void
    {
        $this->seed(TestimonialSeeder::class);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Abdul Haris Haris')
            ->assertSee('syamsuri uwie')
            ->assertSee('marthadys 1103')
            ->assertSee('Ulasan Google')
            ->assertSee('AH', false)
            ->assertSee('makasih Banjar Custom.');
    }

    public function test_profile_requires_current_password_and_strong_new_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => Hash::make('passwordlama')]);

        $this->actingAs($admin)->put(route('admin.profile.update'), [
            'current_password' => 'passwordsalah',
            'password' => 'passwordbaruu',
            'password_confirmation' => 'passwordbaruu',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($admin)->put(route('admin.profile.update'), [
            'current_password' => 'passwordlama',
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors('password');

        $this->actingAs($admin->fresh())->put(route('admin.profile.update'), [
            'current_password' => 'passwordlama',
            'password' => 'passwordbaruu',
            'password_confirmation' => 'passwordbaruu',
        ])->assertRedirect(route('admin.profile.edit'));

        $this->assertTrue(Hash::check('passwordbaruu', $admin->fresh()->password));
    }

    public function test_order_status_change_records_history(): void
    {
        $order = $this->order();
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'production'])
            ->assertRedirect(route('admin.orders.show', $order));

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'completed'])
            ->assertRedirect(route('admin.orders.show', $order));

        $history = $order->statusHistory()->with('changer')->get();

        $this->assertCount(3, $history);
        $this->assertNull($history[0]->from_status);
        $this->assertSame(CustomOrder::STATUS_PENDING, $history[0]->to_status);
        $this->assertSame(CustomOrder::STATUS_PENDING, $history[1]->from_status);
        $this->assertSame(CustomOrder::STATUS_PRODUCTION, $history[1]->to_status);
        $this->assertSame($admin->id, $history[1]->changed_by);
        $this->assertSame(CustomOrder::STATUS_PRODUCTION, $history[2]->from_status);
        $this->assertSame(CustomOrder::STATUS_COMPLETED, $history[2]->to_status);
    }

    public function test_order_print_page_shows_order_summary(): void
    {
        $order = $this->order(['quantity' => 10]);

        $this->actingAs($this->admin())
            ->get(route('admin.orders.print', $order))
            ->assertOk()
            ->assertSee('Pelanggan Konten')
            ->assertSee('10')
            ->assertSee('id="btn-cetak"', false)
            ->assertSee('<script nonce="', false)
            ->assertDontSee('onclick=', false);
    }

    public function test_order_export_downloads_csv_with_orders(): void
    {
        $this->order(['name' => 'Pelanggan Export']);

        $response = $this->actingAs($this->admin())->get(route('admin.orders.export'));

        $response->assertOk();

        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Pelanggan Export', $response->streamedContent());
    }

    public function test_order_export_filters_by_status(): void
    {
        $this->order(['name' => 'Export Pending']);

        $selesai = $this->order(['name' => 'Export Selesai']);
        $selesai->forceFill(['status' => CustomOrder::STATUS_COMPLETED])->save();

        $csv = $this->actingAs($this->admin())
            ->get(route('admin.orders.export', ['status' => 'completed']))
            ->streamedContent();

        $this->assertStringContainsString('Export Selesai', $csv);
        $this->assertStringNotContainsString('Export Pending', $csv);
    }

    public function test_order_export_searches_by_name(): void
    {
        $this->order(['name' => 'Dicari Sobat']);
        $this->order(['name' => 'Tidak Terlibat']);

        $csv = $this->actingAs($this->admin())
            ->get(route('admin.orders.export', ['q' => 'Sobat']))
            ->streamedContent();

        $this->assertStringContainsString('Dicari Sobat', $csv);
        $this->assertStringNotContainsString('Tidak Terlibat', $csv);
    }

    public function test_guest_cannot_access_new_admin_pages(): void
    {
        foreach ([
            'admin.faqs.index',
            'admin.testimonials.index',
            'admin.settings.edit',
            'admin.profile.edit',
            'admin.orders.export',
        ] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_non_admin_cannot_access_new_admin_pages(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        foreach (['admin.faqs.index', 'admin.testimonials.index', 'admin.settings.edit', 'admin.profile.edit'] as $route) {
            $this->actingAs($user)->get(route($route))->assertForbidden();
        }
    }
}
