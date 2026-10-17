<x-layout title="Регистрация">
    <div class="login-grid">
        <section class="login-intro">
            <p class="eyebrow">КОМПЬЮТЕРНЫЙ КЛУБ · САРАТОВ</p>
            <h1>Начните с места.<br>Остальное — <span>за вами.</span></h1>
            <p class="lead">Создайте учётную запись, выберите компьютер и запланируйте визит. Все ваши бронирования будут в одном месте.</p>
            <a class="text-link" href="{{ route('computers.index') }}">Сначала посмотреть компьютеры →</a>
        </section>
        <section class="panel login-panel">
            <p class="eyebrow">НОВЫЙ КЛИЕНТ</p>
            <h2>Добро пожаловать</h2>
            <p class="muted">Заполните четыре поля — и можно бронировать.</p>
            <form action="{{ route('register.store') }}" method="POST" class="form-stack">
                @csrf
                <div class="field">
                    <label for="name">Имя</label>
                    <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="80" required
                           @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    <x-field-error name="name" />
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                           autocomplete="username" maxlength="254" required placeholder="name@example.com"
                           @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    <x-field-error name="email" />
                </div>
                <div class="field">
                    <label for="password">Пароль</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" maxlength="72" required
                           aria-describedby="password-hint @error('password') password-error @enderror"
                           @error('password') aria-invalid="true" @enderror>
                    <p id="password-hint" class="field-hint">Не менее 8 символов.</p>
                    <x-field-error name="password" />
                </div>
                <div class="field">
                    <label for="password_confirmation">Подтвердите пароль</label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                           autocomplete="new-password" minlength="8" maxlength="72" required
                           @error('password_confirmation') aria-invalid="true" aria-describedby="password_confirmation-error" @enderror>
                    <x-field-error name="password_confirmation" />
                </div>
                <button class="button button-primary button-full" type="submit">Создать учётную запись <span>→</span></button>
            </form>
            <p class="form-note">Уже зарегистрированы? <a class="text-link" href="{{ route('login') }}">Войти</a></p>
        </section>
    </div>
</x-layout>
