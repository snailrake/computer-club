<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Computer;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTimeTest extends TestCase
{
    use RefreshDatabase;

    private User $client;
    private Computer $computer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-10-18 09:30:00', 'UTC'));
        $this->client = User::factory()->create();
        $this->computer = Computer::query()->create(['name' => 'PC-TIME', 'is_active' => true]);
        $this->actingAs($this->client);
    }

    public function test_saratov_input_is_stored_in_utc_and_round_trips_without_a_second_conversion(): void
    {
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('Europe/Saratov', config('app.club_timezone'));
        $period = ['computer_id' => $this->computer->id, 'starts_at' => '2026-10-19T00:30', 'ends_at' => '2026-10-19T01:30'];
        $this->post('/bookings', $period)->assertRedirect();
        $booking = Booking::query()->sole();
        $this->assertSame('2026-10-18 20:30:00', $booking->getRawOriginal('starts_at'));
        $this->assertSame('2026-10-18 21:30:00', $booking->getRawOriginal('ends_at'));
        $this->assertSame('UTC', $booking->starts_at->timezoneName);
        $this->get(route('bookings.show', $booking))->assertOk()->assertSee('19.10.2026 · 00:30')->assertSee('Саратов (UTC+4)');
        $this->get(route('bookings.edit', $booking))->assertOk()->assertSee('2026-10-19T00:30')->assertSee('2026-10-19T01:30');
        $this->patch(route('bookings.update', $booking), $period)->assertRedirect();
        $this->assertSame('2026-10-18 20:30:00', $booking->fresh()->getRawOriginal('starts_at'));
        $this->assertSame('00:30', $booking->startsAtInClubTime()->format('H:i'));
        $this->assertSame('UTC', $booking->starts_at->timezoneName);
    }

    public function test_future_validation_uses_saratov_time_including_the_four_hour_boundary(): void
    {
        $this->postJson('/bookings', [
            'computer_id' => $this->computer->id, 'starts_at' => '2026-10-18T13:15', 'ends_at' => '2026-10-18T14:15',
        ])->assertUnprocessable()->assertJsonValidationErrors('starts_at');
        $this->post('/bookings', [
            'computer_id' => $this->computer->id, 'starts_at' => '2026-10-18T13:45', 'ends_at' => '2026-10-18T14:45',
        ])->assertRedirect();
        $this->assertSame('2026-10-18 09:45:00', Booking::query()->sole()->getRawOriginal('starts_at'));
        $this->get('/bookings/create')->assertSee('min="2026-10-18T13:31"', escape: false);
    }

    public function test_owner_can_change_a_past_booking_to_a_future_period(): void
    {
        $booking = new Booking([
            'computer_id' => $this->computer->id,
            'starts_at' => now()->subHours(2), 'ends_at' => now()->subHour(),
        ]);
        $this->client->bookings()->save($booking);

        $this->get(route('bookings.show', $booking))->assertOk()->assertSee('Изменить бронирование');
        $this->get(route('bookings.edit', $booking))->assertOk();
        $this->patch(route('bookings.update', $booking), [
            'computer_id' => $this->computer->id, 'starts_at' => '2026-10-19T15:00', 'ends_at' => '2026-10-19T16:00',
        ])->assertRedirect();
        $this->assertSame('2026-10-19 11:00:00', $booking->fresh()->getRawOriginal('starts_at'));
        $this->assertSame($this->client->id, $booking->fresh()->user_id);
    }

    public function test_owner_can_delete_a_past_booking(): void
    {
        $booking = new Booking([
            'computer_id' => $this->computer->id,
            'starts_at' => now()->subHours(2), 'ends_at' => now()->subHour(),
        ]);
        $this->client->bookings()->save($booking);

        $this->delete(route('bookings.destroy', $booking))->assertRedirect('/bookings');
        $this->assertModelMissing($booking);
    }

    public function test_old_dates_are_migrated_without_changing_the_local_visit_or_account_data(): void
    {
        $this->client->forceFill([
            'created_at' => '2026-10-17 15:23:58', 'updated_at' => '2026-10-17 15:23:58',
            'email_verified_at' => '2026-10-17 16:00:00',
        ])->save();
        $booking = new Booking(['computer_id' => $this->computer->id, 'starts_at' => '2026-10-20 18:00:00', 'ends_at' => '2026-10-20 19:00:00']);
        $this->client->bookings()->save($booking);
        $passwordHash = $this->client->password;
        $migration = require database_path('migrations/2026_10_17_000006_convert_local_timestamps_to_utc.php');
        $migration->up();
        $booking->refresh();
        $this->assertSame('2026-10-20 14:00:00', $booking->getRawOriginal('starts_at'));
        $this->assertSame('2026-10-20 15:00:00', $booking->getRawOriginal('ends_at'));
        $this->assertSame('2026-10-20 18:00:00', $booking->startsAtInClubTime()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-17 11:23:58', $this->client->fresh()->getRawOriginal('created_at'));
        $this->assertSame('2026-10-17 12:00:00', $this->client->fresh()->getRawOriginal('email_verified_at'));
        $this->assertSame($passwordHash, $this->client->fresh()->password);
        $this->assertSame($this->client->id, $booking->user_id);
        $migration->down();
        $this->assertSame('2026-10-20 18:00:00', $booking->fresh()->getRawOriginal('starts_at'));
        $this->assertDatabaseCount('bookings', 1);
    }
}
