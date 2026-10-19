@props(['title'])
<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} · Компьютерный клуб</title>
        <link rel="stylesheet" href="{{ asset('styles.css') }}">
    </head>
    <body>
        <header class="site-header">
            <div class="header-inner">
                <a class="brand" href="{{ route('computers.index') }}">Компьютерный клуб</a>
                <nav aria-label="Основная навигация">
                    <a href="{{ route('computers.index') }}">Компьютеры</a>
                    @auth
                        <a href="{{ route('bookings.index') }}">{{ auth()->user()->isAdmin() ? 'Все бронирования' : 'Мои бронирования' }}</a>
                    @endauth
                </nav>
                @auth
                    <div class="account">
                        <span>{{ auth()->user()->name }} · {{ auth()->user()->role->label() }}</span>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="secondary" type="submit">Выйти</button>
                        </form>
                    </div>
                @else
                    <a class="button secondary" href="{{ route('login') }}">Войти</a>
                @endauth
            </div>
        </header>
        <main class="container">
            @if (session('success'))
                <p class="notice success" role="status">{{ session('success') }}</p>
            @endif
            @if (isset($errors) && $errors->any())
                <div class="notice error" role="alert">
                    <strong>Проверьте данные</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {{ $slot }}
        </main>
        <footer>Учебный проект · Саратов (UTC+4)</footer>
    </body>
</html>
