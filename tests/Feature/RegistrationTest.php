<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Computer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_client_registers_is_authenticated_and_returns_to_booking(): void
    {
        $computer = Computer::query()->create(['name' => 'PC-01', 'is_active' => true]);
        $destination = route('bookings.create', ['computer' => $computer->id]);
        $this->get('/')->assertRedirect('/computers');
        $this->get('/computers')->assertOk()->assertSee('Регистрация');
        $this->get(route('computers.show', $computer))->assertOk()->assertSee('Забронировать компьютер');
        $this->get($destination)->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee(route('register'));
        $this->get('/register')->assertOk()->assertSee('Подтвердите пароль');
        $sessionId = session()->getId();

        $this->post('/register', [...$this->account(), 'email' => ' New.Client@Club.Test '])->assertRedirect($destination);
        $user = User::query()->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::Client, $user->role);
        $this->assertSame('new.client@club.test', $user->email);
        $this->assertNotSame('ClientPass2026!', $user->getRawOriginal('password'));
        $this->assertTrue(Hash::check('ClientPass2026!', $user->password));
        $this->assertNotSame($sessionId, session()->getId());
        $this->get($destination)->assertOk()->assertViewHas('selectedComputerId', $computer->id);
        $this->get('/computers/create')->assertForbidden();
        $this->post('/logout')->assertRedirect('/computers');
        $this->assertGuest();
        $this->post('/login', ['email' => 'NEW.CLIENT@CLUB.TEST', 'password' => 'ClientPass2026!'])->assertRedirect('/computers');
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_rejects_duplicate_email_regardless_of_input_case(): void
    {
        User::factory()->create(['email' => 'new.client@club.test']);
        $this->from('/register')->post('/register', [...$this->account(), 'email' => 'NEW.CLIENT@CLUB.TEST'])
            ->assertRedirect('/register');
        $this->get('/register')->assertOk()->assertSee('Этот email уже зарегистрирован');
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_invalid_registration_keeps_safe_fields_but_never_passwords(): void
    {
        $this->from('/register')->post('/register', [
            'name' => '', 'email' => 'invalid', 'password' => 'short', 'password_confirmation' => 'different',
        ])->assertRedirect('/register');
        $this->get('/register')->assertOk()->assertSee('invalid')->assertSee('Укажите корректный email')
            ->assertDontSee('value="short"', escape: false);
        $this->assertNull(session()->getOldInput('password'));
        $this->assertNull(session()->getOldInput('password_confirmation'));
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();

        $this->postJson('/register', [...$this->account(), 'password_confirmation' => 'different'])
            ->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->postJson('/register', [...$this->account(), 'name' => str_repeat('a', 81)])
            ->assertJsonValidationErrors('name');
        $this->postJson('/register', [...$this->account(), 'email' => [], 'password' => [], 'password_confirmation' => []])
            ->assertUnprocessable()->assertJsonValidationErrors(['email', 'password', 'password_confirmation']);
    }

    public function test_client_cannot_register_as_admin_or_supply_another_owner(): void
    {
        $this->postJson('/register', [...$this->account(), 'role' => 'admin', 'user_id' => 1])
            ->assertUnprocessable()->assertJsonValidationErrors(['role', 'user_id']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_byte_limit_and_null_bytes_are_rejected_without_server_errors(): void
    {
        foreach ([str_repeat('я', 37), "ValidPass\0word2026!"] as $password) {
            $this->postJson('/register', [...$this->account(), 'password' => $password, 'password_confirmation' => $password])
                ->assertUnprocessable()->assertJsonValidationErrors('password');
        }

        $this->assertDatabaseCount('users', 0);
        $this->post('/register', [...$this->account(), 'password' => 'НадёжныйПароль2026!', 'password_confirmation' => 'НадёжныйПароль2026!'])
            ->assertRedirect('/computers');
        $this->assertTrue(Hash::check('НадёжныйПароль2026!', User::query()->sole()->password));
    }

    public function test_authenticated_users_cannot_register_again_and_existing_accounts_are_unchanged(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $original = $admin->getAttributes();
        $this->actingAs($admin)->get('/register')->assertRedirect('/');
        $this->post('/register', $this->account())->assertRedirect('/');
        $this->assertDatabaseCount('users', 1);
        $this->assertEquals($original, $admin->fresh()->getAttributes());
    }

    private function account(): array
    {
        return [
            'name' => 'Новый клиент',
            'email' => 'new.client@club.test',
            'password' => 'ClientPass2026!',
            'password_confirmation' => 'ClientPass2026!',
        ];
    }
}
