<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Computer;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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

    public function store(BookingRequest $request, BookingService $service): RedirectResponse
    {
        $booking = new Booking();
        $booking->user()->associate($request->user());
        $service->save(
            $booking,
            $request->integer('computer_id'),
            $request->validated('starts_at'),
            $request->validated('ends_at'),
        );

        return redirect()->route('bookings.show', $booking)->with('success', 'Бронирование оформлено. Компьютер закреплён за вами на выбранное время.');
    }

    public function show(Booking $booking): View
    {
        Gate::authorize('view', $booking);
        $booking->load(['computer', 'user']);

        return view('bookings.show', compact('booking'));
    }

    public function edit(Booking $booking): View
    {
        Gate::authorize('update', $booking);

        return view('bookings.edit', [
            'booking' => $booking->load(['computer', 'user']),
            'computers' => Computer::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedComputerId' => $booking->computer_id,
        ]);
    }

    public function update(BookingRequest $request, Booking $booking, BookingService $service): RedirectResponse
    {
        $service->save(
            $booking,
            $request->integer('computer_id'),
            $request->validated('starts_at'),
            $request->validated('ends_at'),
        );

        return redirect()->route('bookings.show', $booking)->with('success', 'Бронирование изменено. Выбранное время забронировано.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        DB::transaction(function () use ($booking): void {
            $booking->refresh();
            Gate::authorize('delete', $booking);
            $booking->delete();
        });

        $message = $booking->starts_at->isFuture()
            ? 'Бронирование отменено. Время снова доступно.'
            : 'Запись о бронировании удалена.';

        return redirect()->route('bookings.index')->with('success', $message);
    }
}
