<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_displayed_for_guests(): void
    {
        $this->get('/login')->assertOk()->assertSee('Maivile');
    }

    public function test_admin_can_login_with_email_and_is_sent_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@test.dev']);

        $this->post('/login', ['login' => 'admin@test.dev', 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('home'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_student_can_login_with_nis_and_is_sent_to_student_dashboard(): void
    {
        $student = User::factory()->create(['nis' => '2024999']);

        $this->post('/login', ['login' => '2024999', 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($student);
        $this->get(route('home'))->assertRedirect(route('student.dashboard'));
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['nis' => '2024999']);

        $this->from('/login')
            ->post('/login', ['login' => '2024999', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_login_fails_for_unknown_account_with_same_message(): void
    {
        $this->post('/login', ['login' => 'tidakada', 'password' => 'password'])
            ->assertSessionHasErrors(['login' => 'Email/NIS/NIP atau kata sandi salah.']);

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        User::factory()->inactive()->create(['nis' => '2024888']);

        $this->post('/login', ['login' => '2024888', 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failures(): void
    {
        User::factory()->create(['nis' => '2024777']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['login' => '2024777', 'password' => 'salah']);
        }

        // Percobaan ke-6 diblokir meskipun password benar.
        $this->post('/login', ['login' => '2024777', 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_logout_requires_post(): void
    {
        $this->actingAs(User::factory()->create())->get('/logout')->assertStatus(405);
    }

    public function test_user_with_initial_password_is_forced_to_change_it(): void
    {
        $student = User::factory()->mustChangePassword()->create();

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertRedirect(route('password.edit'));

        $this->actingAs($student)->get(route('password.edit'))->assertOk();
    }
}
