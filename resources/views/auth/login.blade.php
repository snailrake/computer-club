<x-layout title="Вход">
    <div class="login-grid">
        <section class="login-intro">
            <p class="eyebrow">КОМПЬЮТЕРНЫЙ КЛУБ · САРАТОВ</p>
            <h1>Ваше время.<br>Ваше <span>место в игре.</span></h1>
            <p class="lead">Войдите, чтобы оформить бронь или изменить планы. Если вы уже выбрали компьютер, после входа вернётесь к оформлению.</p>
            <div class="intro-line"><span class="status-dot"></span> Бронь сохраняется сразу после подтверждения</div>
        </section>
        <section class="panel login-panel">
            <p class="eyebrow">ЛИЧНЫЙ КАБИНЕТ</p>
            <h2>С возвращением</h2>
            <p class="muted">Введите данные вашей учётной записи.</p>
            <form action="{{ route('login.store') }}" method="POST" class="form-stack">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                           autocomplete="username" maxlength="254" required placeholder="name@example.com"
                           @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    <x-field-error name="email" />
                </div>
                <div class="field">
                    <label for="password">Пароль</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" maxlength="1024" required
                           @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                    <x-field-error name="password" />
                </div>
                <button class="button button-primary button-full" type="submit">Войти в клуб <span>→</span></button>
            </form>
            <p class="form-note">Впервые у нас? <a class="text-link" href="{{ route('register') }}">Зарегистрироваться</a></p>
        </section>
    </div>
</x-layout>
