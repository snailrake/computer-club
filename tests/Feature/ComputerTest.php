<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Computer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComputerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_complete_computer_crud(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
        $this->get('/computers/create')->assertOk();
        $this->post('/computers', ['name' => 'PC-TEST', 'description' => 'Description', 'is_active' => 1])->assertRedirect();

        $computer = Computer::query()->sole();
        $this->get('/computers')->assertOk()->assertSee('PC-TEST');
        $this->get(route('computers.show', $computer))->assertOk()->assertSee('Description');
        $this->get(route('computers.edit', $computer))->assertOk();
        $this->patch(route('computers.update', $computer), ['name' => 'PC-UPDATED', 'description' => 'Updated', 'is_active' => 0])
            ->assertRedirect(route('computers.show', $computer));
        $this->assertDatabaseHas('computers', ['id' => $computer->id, 'name' => 'PC-UPDATED', 'is_active' => 0]);
        $this->delete(route('computers.destroy', $computer))->assertRedirect('/computers');
        $this->assertModelMissing($computer);
    }

    public function test_client_can_read_but_cannot_manage_computers(): void
    {
        $computer = Computer::query()->create(['name' => 'PC-01', 'is_active' => true]);
        $this->actingAs(User::factory()->create());
        $this->get('/computers')->assertOk()->assertDontSee('Добавить компьютер');
        $this->get(route('computers.show', $computer))->assertOk();
        $this->get('/computers/create')->assertForbidden();
        $this->post('/computers', ['name' => 'PC-02', 'is_active' => 1])->assertForbidden();
        $this->get(route('computers.edit', $computer))->assertForbidden();
        $this->patch(route('computers.update', $computer), ['name' => 'Changed', 'is_active' => 0])->assertForbidden();
        $this->delete(route('computers.destroy', $computer))->assertForbidden();
        $this->assertDatabaseHas('computers', ['id' => $computer->id, 'name' => 'PC-01', 'is_active' => 1]);
    }

    public function test_computer_validation_and_unique_name(): void
    {
        $computer = Computer::query()->create(['name' => 'PC-01', 'is_active' => true]);
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
        $this->postJson('/computers', ['name' => 'PC-01', 'is_active' => 1])->assertJsonValidationErrors('name');
        $this->postJson('/computers', ['name' => str_repeat('a', 81), 'is_active' => 'invalid'])->assertJsonValidationErrors(['name', 'is_active']);
        $this->patch(route('computers.update', $computer), ['name' => 'PC-01', 'is_active' => 1])->assertRedirect();
        $this->assertDatabaseCount('computers', 1);
    }

    public function test_a_computer_with_bookings_cannot_be_deleted(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $computer = Computer::query()->create(['name' => 'PC-01', 'is_active' => true]);
        $booking = new Booking(['computer_id' => $computer->id, 'starts_at' => now()->addDay(), 'ends_at' => now()->addDay()->addHour()]);
        $user->bookings()->save($booking);

        $this->actingAs($user)->from(route('computers.show', $computer))->delete(route('computers.destroy', $computer))
            ->assertRedirect(route('computers.show', $computer))->assertSessionHasErrors('computer');
        $this->assertModelExists($computer);
        $this->assertModelExists($booking);
    }

    public function test_missing_computer_has_a_readable_404_page(): void
    {
        $this->actingAs(User::factory()->create())->get('/computers/99999')->assertNotFound()->assertSee('Страница не найдена');
    }

    public function test_catalog_paginates_and_inactive_computers_cannot_be_booked(): void
    {
        for ($number = 1; $number <= 11; $number++) {
            Computer::query()->create(['name' => sprintf('PC-%02d', $number), 'is_active' => $number !== 11]);
        }

        $this->actingAs(User::factory()->create());
        $this->get('/computers')->assertOk()->assertSee('Страница 1 из 2')->assertDontSee('PC-11');
        $this->get('/computers?page=2')->assertOk()->assertSee('PC-11');
        $computer = Computer::query()->where('name', 'PC-11')->firstOrFail();
        $this->get(route('computers.show', $computer))->assertOk()->assertDontSee('Забронировать компьютер');
    }
}
