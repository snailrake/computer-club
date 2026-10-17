<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SessionController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 6;
    private const LOGIN_DECAY_SECONDS = 60;

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
            'password' => ['required', 'string', 'max:1024'],
        ]);

        $credentials['email'] = Str::lower($credentials['email']);
        $throttleKey = 'login:'.hash('sha256', $credentials['email'].'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages(['email' => "Слишком много попыток. Повторите вход через {$seconds} сек."]);
        }

        if (strlen($credentials['password']) > 72
            || str_contains($credentials['password'], "\0")
            || ! Auth::attempt($credentials)) {
            RateLimiter::hit($throttleKey, self::LOGIN_DECAY_SECONDS);
            throw ValidationException::withMessages(['email' => 'Неверный email или пароль.']);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('computers.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('computers.index')->with('success', 'Вы вышли из учётной записи.');
    }
}
