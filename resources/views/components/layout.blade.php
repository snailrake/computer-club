@props(['title'])
<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} · Компьютерный клуб · Саратов</title>
        <link rel="stylesheet" href="{{ asset('styles.css') }}">
    </head>
    <body>
        <a class="skip-link" href="#main-content">Перейти к содержимому</a>
        <header class="site-header">
            <div class="header-inner">
                <a class="brand" href="{{ route('computers.index') }}">
                    <span class="brand-mark">C<span>↗</span>
                    </span>
                    <span>КОМПЬЮТЕРНЫЙ<br>
                        <strong>КЛУБ</strong>
                    </span>
                </a>
                <nav aria-label="Основная навигация">
                    <a @class(['nav-link', 'active' => request()->routeIs('computers.*')])
                       @if (request()->routeIs('computers.*')) aria-current="page" @endif
                       href="{{ route('computers.index') }}">Компьютеры</a>
                    @auth
                        <a @class(['nav-link', 'active' => request()->routeIs('bookings.*')])
                           @if (request()->routeIs('bookings.*')) aria-current="page" @endif
                           href="{{ route('bookings.index') }}">{{ auth()->user()->isAdmin() ? 'Все бронирования' : 'Мои бронирования' }}</a>
                    @endauth
                </nav>
                @auth
                    <div class="account">
                        <div>
                            <strong>{{ auth()->user()->name }}</strong>
                            <span>{{ auth()->user()->role->label() }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="button button-quiet" type="submit">Выйти</button>
                        </form>
                    </div>
                @else
                    <div class="guest-actions">
                        <a class="nav-link" href="{{ route('login') }}" @if (request()->routeIs('login')) aria-current="page" @endif>Войти</a>
                        <a class="button button-primary" href="{{ route('register') }}">Регистрация</a>
                    </div>
                @endauth
            </div>
        </header>
        <main id="main-content" class="container" tabindex="-1">
            @if (session('success'))
                <div class="notice notice-success" role="status">{{ session('success') }}</div>
            @endif
            @if (isset($errors) && $errors->any())
                <div class="notice notice-error" role="alert">
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
        <footer class="site-footer">
            <div>Компьютерный клуб <span>·</span> Саратов</div>
            <div>Все даты и время — по Саратову (UTC+4)</div>
        </footer>
    </body>
</html>
