<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Computer;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::query()->with(['computer', 'user'])->orderByDesc('starts_at');

        if (! $request->user()->isAdmin()) {
            $bookings->where('user_id', $request->user()->id);
        }

        return view('bookings.index', ['bookings' => $bookings->paginate(10)]);
    }

    public function create(Request $request): View
    {
        return view('bookings.create', [
            'booking' => new Booking(),
            'computers' => Computer::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedComputerId' => $request->integer('computer'),
        ]);
    }

    public function store(BookingRequest $request): RedirectResponse
    {
        $booking = new Booking();
        $booking->user()->associate($request->user());
        $this->saveBooking($request, $booking);

        return redirect()->route('bookings.show', $booking)->with('success', 'Бронирование создано.');
    }

    public function show(Booking $booking): View
    {
        Gate::authorize('view', $booking);

        return view('bookings.show', ['booking' => $booking->load(['computer', 'user'])]);
    }

    public function edit(Booking $booking): View
    {
        Gate::authorize('update', $booking);

        return view('bookings.edit', [
            'booking' => $booking->load('computer'),
            'computers' => Computer::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedComputerId' => $booking->computer_id,
        ]);
    }

    public function update(BookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->saveBooking($request, $booking);

        return redirect()->route('bookings.show', $booking)->with('success', 'Бронирование изменено.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        Gate::authorize('delete', $booking);
        $booking->delete();

        return redirect()->route('bookings.index')->with('success', 'Бронирование удалено.');
    }

    private function saveBooking(BookingRequest $request, Booking $booking): void
    {
        $booking->fill([
            'computer_id' => $request->integer('computer_id'),
            'starts_at' => CarbonImmutable::parse($request->validated('starts_at'), config('app.club_timezone'))->utc(),
            'ends_at' => CarbonImmutable::parse($request->validated('ends_at'), config('app.club_timezone'))->utc(),
        ]);

        if (! $booking->starts_at->isFuture()) {
            throw ValidationException::withMessages(['starts_at' => 'Начало брони должно быть в будущем.']);
        }

        $conflictingBookings = Booking::query()
            ->where('computer_id', $booking->computer_id)
            ->where('starts_at', '<', $booking->ends_at)
            ->where('ends_at', '>', $booking->starts_at);

        if ($booking->exists) {
            $conflictingBookings->whereKeyNot($booking->id);
        }

        if ($conflictingBookings->exists()) {
            throw ValidationException::withMessages(['starts_at' => 'Компьютер уже забронирован на часть этого периода.']);
        }

        $booking->save();
    }
}
