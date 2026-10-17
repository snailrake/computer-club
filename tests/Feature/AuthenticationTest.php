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
        $this->get('/computers')->assertOk()->assertSee('САРАТОВ');
        $this->get('/bookings')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('С возвращением');
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

    public function test_login_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->postJson('/login', ['email' => 'missing@club.test', 'password' => 'incorrect'])
                ->assertUnprocessable();
        }

        $response = $this->postJson('/login', ['email' => 'missing@club.test', 'password' => 'incorrect'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertStringContainsString('Слишком много попыток', $response->json('errors.email.0'));
        $this->assertGuest();
        $this->travel(61)->seconds();
        $this->postJson('/login', ['email' => 'missing@club.test', 'password' => 'incorrect'])
            ->assertUnprocessable()->assertJsonPath('errors.email.0', 'Неверный email или пароль.');
    }

    public function test_successful_login_clears_failures_and_returns_to_the_selected_computer(): void
    {
        $user = User::factory()->create();
        $destination = route('bookings.create', ['computer' => 7]);
        $this->get($destination)->assertRedirect('/login');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/login', ['email' => strtoupper($user->email), 'password' => 'incorrect'])
                ->assertUnprocessable();
        }

        $sessionId = session()->getId();
        $this->post('/login', ['email' => strtoupper($user->email), 'password' => 'password'])->assertRedirect($destination);
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, session()->getId());
        $this->post('/logout')->assertRedirect('/computers');

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/computers');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_does_not_accept_a_truncated_bcrypt_password(): void
    {
        $user = User::factory()->create(['password' => str_repeat('a', 72)]);
        $this->postJson('/login', ['email' => $user->email, 'password' => str_repeat('a', 72).'wrong-suffix'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertGuest();
    }
}
