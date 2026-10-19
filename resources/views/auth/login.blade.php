<x-layout title="Вход">
    <h1>Вход</h1>
    <form class="panel form-panel" action="{{ route('login.store') }}" method="POST">
        @csrf
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" maxlength="254" required>
        </div>
        <div class="field">
            <label for="password">Пароль</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button type="submit">Войти</button>
    </form>
</x-layout>
