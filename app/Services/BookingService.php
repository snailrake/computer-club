<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\Computer;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function save(Booking $booking, int $computerId, string $localStart, string $localEnd): void
    {
        $startsAt = CarbonImmutable::createFromFormat(
            '!'.Booking::INPUT_DATE_FORMAT,
            $localStart,
            config('app.club_timezone'),
        )->utc();
        $endsAt = CarbonImmutable::createFromFormat(
            '!'.Booking::INPUT_DATE_FORMAT,
            $localEnd,
            config('app.club_timezone'),
        )->utc();

        DB::transaction(function () use ($booking, $computerId, $startsAt, $endsAt): void {
            if ($booking->exists) {
                $booking->refresh();

                if (! $booking->starts_at->isFuture()) {
                    throw ValidationException::withMessages([
                        'starts_at' => 'Начавшееся или прошедшее бронирование нельзя перенести.',
                    ]);
                }
            }

            $computer = Computer::query()->find($computerId);

            if ($computer === null || ! $computer->is_active) {
                throw ValidationException::withMessages([
                    'computer_id' => 'Этот компьютер больше не принимает бронирования. Выберите другой.',
                ]);
            }

            $booking->fill([
                'computer_id' => $computerId,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);

            if (! $booking->starts_at->isFuture() || $booking->ends_at <= $booking->starts_at) {
                throw ValidationException::withMessages([
                    'starts_at' => 'Выберите будущий период с окончанием позже начала.',
                ]);
            }

            $conflictingBookings = Booking::query()
                ->where('computer_id', $computer->id)
                ->where('starts_at', '<', $booking->ends_at)
                ->where('ends_at', '>', $booking->starts_at);

            if ($booking->exists) {
                $conflictingBookings->whereKeyNot($booking->id);
            }

            if ($conflictingBookings->exists()) {
                throw ValidationException::withMessages([
                    'starts_at' => 'Компьютер уже забронирован на часть этого периода. Выберите другое время или компьютер.',
                ]);
            }

            $booking->save();
        }, attempts: 3);
    }
}
