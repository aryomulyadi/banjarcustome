<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_form_validation_messages_are_indonesian(): void
    {
        $this->from(route('pesan.create'))->post(route('pesan.store'), [])->assertRedirect(route('pesan.create'));

        $page = $this->get(route('pesan.create'));

        $page->assertSee('nama wajib diisi');
        $page->assertSee('nomor WhatsApp wajib diisi');
        $page->assertDontSee('field is required');
    }

    public function test_login_validation_messages_are_indonesian(): void
    {
        $this->post(route('login'), ['email' => 'bukan-email', 'password' => ''])->assertSessionHasErrors('email');

        $page = $this->get(route('login'));

        $page->assertSee('email harus berupa alamat email yang valid');
        $page->assertDontSee('field is required');
    }
}
