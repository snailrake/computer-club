<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ComputerRequest;
use App\Models\Computer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ComputerController extends Controller
{
    public function index(): View
    {
        return view('computers.index', ['computers' => Computer::query()->orderBy('name')->paginate(9)]);
    }

    public function create(): View
    {
        Gate::authorize('create', Computer::class);

        return view('computers.create', ['computer' => new Computer(['is_active' => true])]);
    }

    public function store(ComputerRequest $request): RedirectResponse
    {
        $computer = Computer::query()->create($request->validated());

        return redirect()->route('computers.show', $computer)->with('success', 'Компьютер добавлен.');
    }

    public function show(Computer $computer): View
    {
        $upcomingBookings = $computer->bookings()->where('ends_at', '>', now())->orderBy('starts_at')->limit(8)->get();

        return view('computers.show', compact('computer', 'upcomingBookings'));
    }

    public function edit(Computer $computer): View
    {
        Gate::authorize('update', $computer);

        return view('computers.edit', compact('computer'));
    }

    public function update(ComputerRequest $request, Computer $computer): RedirectResponse
    {
        $computer->update($request->validated());

        return redirect()->route('computers.show', $computer)->with('success', 'Компьютер обновлён.');
    }

    public function destroy(Computer $computer): RedirectResponse
    {
        Gate::authorize('delete', $computer);

        DB::transaction(function () use ($computer): void {
            if ($computer->bookings()->exists()) {
                throw ValidationException::withMessages([
                    'computer' => 'У компьютера есть бронирования. Сначала удалите их или отключите приём новых броней.',
                ]);
            }

            $computer->delete();
        });

        return redirect()->route('computers.index')->with('success', 'Компьютер удалён.');
    }
}
