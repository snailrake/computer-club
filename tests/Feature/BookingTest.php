<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Computer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    private Computer $computer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = User::factory()->create();
        $this->computer = Computer::query()->create(['name' => 'PC-01', 'is_active' => true]);
        $this->actingAs($this->client);
    }

    public function test_client_can_complete_booking_crud(): void
    {
        $this->get('/bookings/create')->assertOk()->assertSee('Новое бронирование');
        $this->post('/bookings', $this->period('10:00', '11:00'))->assertRedirect();
        $booking = Booking::query()->sole();

        $this->assertSame($this->client->id, $booking->user->id);
        $this->assertSame($this->computer->id, $booking->computer->id);
        $this->get('/bookings')->assertOk()->assertSee('PC-01');
        $this->get(route('bookings.show', $booking))->assertOk()->assertSee('10:00');
        $this->get(route('bookings.edit', $booking))->assertOk();
        $this->patch(route('bookings.update', $booking), $this->period('12:00', '13:00'))->assertRedirect();
        $this->assertSame('12:00', $booking->fresh()->startsAtInClubTime()->format('H:i'));
        $this->delete(route('bookings.destroy', $booking))->assertRedirect('/bookings');
        $this->assertModelMissing($booking);
    }

    public function test_all_overlap_shapes_are_rejected_but_adjacent_periods_are_allowed(): void
    {
        $this->post('/bookings', $this->period('10:00', '11:00'))->assertRedirect();

        foreach ([['09:30', '10:30'], ['10:00', '11:00'], ['10:15', '10:45'], ['09:00', '12:00']] as [$start, $end]) {
            $this->postJson('/bookings', $this->period($start, $end))
                ->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        }

        $this->assertDatabaseCount('bookings', 1);
        $this->post('/bookings', $this->period('09:00', '10:00'))->assertRedirect();
        $this->post('/bookings', $this->period('11:00', '12:00'))->assertRedirect();
        $this->assertDatabaseCount('bookings', 3);
    }

    public function test_conflict_on_update_keeps_the_original_booking_and_ignores_its_own_interval(): void
    {
        $this->post('/bookings', $this->period('10:00', '11:00'))->assertRedirect();
        $booking = Booking::query()->sole();
        $this->patch(route('bookings.update', $booking), $this->period('10:00', '11:00'))->assertRedirect();
        $this->post('/bookings', $this->period('12:00', '13:00'))->assertRedirect();
        $this->patchJson(route('bookings.update', $booking), $this->period('12:30', '13:30'))
            ->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        $this->assertSame('10:00', $booking->fresh()->startsAtInClubTime()->format('H:i'));
    }

    public function test_different_computers_can_be_booked_for_the_same_time(): void
    {
        $secondComputer = Computer::query()->create(['name' => 'PC-02', 'is_active' => true]);
        $this->post('/bookings', $this->period('10:00', '11:00'))->assertRedirect();
        $this->post('/bookings', [...$this->period('10:00', '11:00'), 'computer_id' => $secondComputer->id])->assertRedirect();
        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_invalid_periods_and_computers_are_rejected(): void
    {
        foreach ([['10:00', '10:00'], ['11:00', '10:00']] as [$start, $end]) {
            $this->postJson('/bookings', $this->period($start, $end))->assertJsonValidationErrors('ends_at');
        }

        $this->postJson('/bookings', [...$this->period('10:00', '11:00'), 'starts_at' => now()->subDay()->format(Booking::INPUT_DATE_FORMAT)])
            ->assertJsonValidationErrors('starts_at');
        $this->postJson('/bookings', [...$this->period('10:00', '11:00'), 'starts_at' => '2030-02-30T10:00'])
            ->assertJsonValidationErrors('starts_at');
        $this->postJson('/bookings', [...$this->period('10:00', '11:00'), 'computer_id' => 99999])
            ->assertJsonValidationErrors('computer_id');
        $this->computer->update(['is_active' => false]);
        $this->postJson('/bookings', $this->period('10:00', '11:00'))->assertJsonValidationErrors('computer_id');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_client_cannot_read_update_or_delete_another_clients_booking(): void
    {
        $this->post('/bookings', $this->period('10:00', '11:00'))->assertRedirect();
        $booking = Booking::query()->sole();
        $this->actingAs(User::factory()->create());

        $this->get('/bookings')->assertOk()->assertDontSee('PC-01');
        $this->get(route('bookings.show', $booking))->assertForbidden();
        $this->get(route('bookings.edit', $booking))->assertForbidden();
        $this->patch(route('bookings.update', $booking), $this->period('12:00', '13:00'))->assertForbidden();
        $this->delete(route('bookings.destroy', $booking))->assertForbidden();
        $this->assertModelExists($booking);
        $this->assertSame('10:00', $booking->fresh()->startsAtInClubTime()->format('H:i'));
    }

    public function test_admin_can_manage_a_clients_booking_without_changing_its_owner(): void
    {
        $this->post('/bookings', $this->period('10:00', '11:00'))->assertRedirect();
        $booking = Booking::query()->sole();
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
        $this->get('/bookings')->assertOk()->assertSee($this->client->name);
        $this->get(route('bookings.show', $booking))->assertOk();
        $this->get(route('bookings.edit', $booking))->assertOk();
        $this->patch(route('bookings.update', $booking), $this->period('12:00', '13:00'))->assertRedirect();
        $this->assertSame($this->client->id, $booking->fresh()->user_id);
        $this->delete(route('bookings.destroy', $booking))->assertRedirect();
        $this->assertModelMissing($booking);
    }

    public function test_owner_and_role_cannot_be_submitted_or_mass_assigned(): void
    {
        $other = User::factory()->create();
        $this->postJson('/bookings', [...$this->period('10:00', '11:00'), 'user_id' => $other->id, 'role' => 'admin'])
            ->assertJsonValidationErrors(['user_id', 'role']);
        $this->assertDatabaseCount('bookings', 0);
        $this->assertFalse($this->client->fresh()->isAdmin());
        $this->post('/bookings', $this->period('10:00', '11:00'))->assertRedirect();
        $booking = Booking::query()->sole();
        $this->patchJson(route('bookings.update', $booking), [...$this->period('10:00', '11:00'), 'user_id' => $other->id])
            ->assertJsonValidationErrors('user_id');
        $this->assertSame($this->client->id, $booking->fresh()->user_id);
    }

    public function test_escaped_computer_content_is_not_rendered_as_html(): void
    {
        $this->computer->update(['description' => '<script>alert(1)</script>']);
        $this->get(route('computers.show', $this->computer))->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', escape: false)
            ->assertDontSee('<script>alert(1)</script>', escape: false);
    }

    public function test_booking_can_move_to_a_different_computer_and_deletion_releases_the_period(): void
    {
        $secondComputer = Computer::query()->create(['name' => 'PC-02', 'is_active' => true]);
        $this->post('/bookings', $this->period('10:00', '11:00'))->assertRedirect();
        $booking = Booking::query()->sole();
        $this->patch(route('bookings.update', $booking), [...$this->period('10:00', '11:00'), 'computer_id' => $secondComputer->id])
            ->assertRedirect();
        $this->assertSame($secondComputer->id, $booking->fresh()->computer_id);
        $this->delete(route('bookings.destroy', $booking))->assertRedirect();
        $this->post('/bookings', [...$this->period('10:00', '11:00'), 'computer_id' => $secondComputer->id])
            ->assertRedirect();
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_no_active_computers_produces_a_readable_empty_state(): void
    {
        $this->computer->update(['is_active' => false]);
        $this->get('/bookings/create')->assertOk()->assertSee('Нет доступных компьютеров')
            ->assertDontSee('Подтвердить бронирование');
    }

    public function test_disabling_a_computer_preserves_its_booking_and_explains_why_it_cannot_be_rebooked(): void
    {
        $this->post('/bookings', $this->period('10:00', '11:00'))->assertRedirect();
        $booking = Booking::query()->sole();
        $this->computer->update(['is_active' => false]);
        $this->get(route('bookings.show', $booking))->assertOk()->assertSee('10:00');
        $this->get(route('bookings.edit', $booking))->assertOk()->assertSee('Нет компьютеров для переноса');
        $this->get(route('bookings.create', ['computer' => $this->computer->id]))->assertOk()
            ->assertSee('Выбранный компьютер больше не принимает бронирования');
        $this->patchJson(route('bookings.update', $booking), $this->period('12:00', '13:00'))
            ->assertUnprocessable()->assertJsonValidationErrors('computer_id');
        $this->assertModelExists($booking);
        $this->assertSame('10:00', $booking->fresh()->startsAtInClubTime()->format('H:i'));
    }

    private function period(string $start, string $end): array
    {
        $day = now()->addDay()->format('Y-m-d');

        return [
            'computer_id' => $this->computer->id,
            'starts_at' => $day.'T'.$start,
            'ends_at' => $day.'T'.$end,
        ];
    }
}
