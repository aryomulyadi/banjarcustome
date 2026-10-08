<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_login_page_returns_successful_response(): void
    {
        $this->get('/login')->assertOk()->assertSee('Login Admin');
    }

    public function test_admin_can_login_and_is_redirected_to_dashboard(): void
    {
        $admin = $this->admin();

        $response = $this->from('/login')->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $admin = $this->admin();

        $response = $this->from('/login')->post('/login', [
            'email' => $admin->email,
            'password' => 'salah-password',
        ]);

        $response->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_records_last_login_at_and_profile_shows_it(): void
    {
        $admin = $this->admin();

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertNotNull($admin->fresh()->last_login_at);

        $this->actingAs($admin->fresh())
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('Login Terakhir');
    }

    public function test_non_admin_account_cannot_login(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_when_accessing_admin(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_non_admin_user_gets_403_on_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Admin Panel');
    }

    public function test_admin_can_logout(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/logout')->assertRedirect(route('home'));

        $this->assertGuest();
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_logged_in_admin_is_redirected_away_from_login_page(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/login')->assertRedirect(route('admin.dashboard'));
    }

    public function test_logged_in_non_admin_is_redirected_home_from_login_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/login')->assertRedirect(route('home'));
    }

    public function test_admin_menu_is_shown_in_header_only_when_logged_in(): void
    {
        $this->get('/')->assertDontSee('Dashboard');

        $admin = $this->admin();

        $this->actingAs($admin)->get('/')->assertSee('Dashboard');
    }

    public function test_login_is_rate_limited_after_six_failed_attempts(): void
    {
        $admin = $this->admin();

        for ($i = 0; $i < 6; $i++) {
            $this->from('/login')->post('/login', [
                'email' => $admin->email,
                'password' => 'wrong-'.$i,
            ])->assertRedirect('/login');
        }

        $this->from('/login')->post('/login', [
            'email' => $admin->email,
            'password' => 'wrong',
        ])->assertStatus(429);
    }
}
