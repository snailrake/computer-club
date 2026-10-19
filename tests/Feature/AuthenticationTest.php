<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_browse_but_must_log_in_to_book(): void
    {
        $this->get('/computers')->assertOk()->assertSee('Компьютерный клуб');
        $this->get('/bookings')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Email')->assertSee('Пароль');
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }

    public function test_both_roles_can_log_in_and_log_out(): void
    {
        foreach (UserRole::cases() as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/computers');
            $this->assertAuthenticatedAs($user);
            $this->get('/computers')->assertOk()->assertSee($role->label());
            $this->post('/logout')->assertRedirect('/computers');
            $this->assertGuest();
        }
    }

    public function test_incorrect_password_does_not_authenticate(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'incorrect'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_uses_standard_rate_limiting(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->postJson('/login', ['email' => 'missing@club.test', 'password' => 'incorrect'])
                ->assertUnprocessable();
        }

        $this->postJson('/login', ['email' => 'missing@club.test', 'password' => 'incorrect'])
            ->assertTooManyRequests()->assertHeader('Retry-After');
        $this->assertGuest();

        $this->travel(61)->seconds();
        $this->postJson('/login', ['email' => 'missing@club.test', 'password' => 'incorrect'])
            ->assertUnprocessable()->assertJsonPath('errors.email.0', 'Неверный email или пароль.');
    }

    public function test_login_regenerates_the_session_and_returns_to_the_selected_computer(): void
    {
        $user = User::factory()->create();
        $destination = route('bookings.create', ['computer' => 7]);
        $this->get($destination)->assertRedirect('/login');
        $sessionId = session()->getId();

        $this->post('/login', ['email' => ' '.strtoupper($user->email).' ', 'password' => 'password'])->assertRedirect($destination);
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, session()->getId());

        $this->post('/logout')->assertRedirect('/computers');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/computers');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_overlong_and_null_byte_passwords(): void
    {
        $user = User::factory()->create(['password' => str_repeat('a', 72)]);

        foreach ([str_repeat('a', 72).'wrong-suffix', "password\0suffix"] as $password) {
            $this->postJson('/login', ['email' => $user->email, 'password' => $password])
                ->assertUnprocessable()->assertJsonValidationErrors('email');
            $this->assertGuest();
        }
    }
}
