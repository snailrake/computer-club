<x-layout :title="$computer->name">
    <a class="back-link" href="{{ route('computers.index') }}">← Все компьютеры</a>
    <section class="page-heading">
        <div>
            <p class="eyebrow">КОМПЬЮТЕР КЛУБА</p>
            <h1>{{ $computer->name }}</h1>
        </div>
        <span class="badge {{ $computer->is_active ? 'badge-green' : 'badge-muted' }}">{{ $computer->is_active ? 'Принимает новые брони' : 'Приём броней отключён' }}</span>
    </section>
    <div class="detail-grid">
        <section class="panel detail-panel">
            <h2>Характеристики и особенности</h2>
            <p class="preserve-lines">{{ $computer->description ?: 'Описание пока не добавлено.' }}</p>
            <p class="muted">Выберите даты и время по Саратову. Доступность периода проверяется при подтверждении бронирования.</p>
            @if ($computer->is_active)
                <a class="button button-primary" href="{{ route('bookings.create', ['computer' => $computer->id]) }}">Забронировать компьютер →</a>
            @else
                <p class="notice notice-neutral">Выберите другой компьютер: это место пока не принимает новые бронирования.</p>
            @endif
            <section class="busy-periods" aria-labelledby="busy-heading">
                <h2 id="busy-heading">Ближайшие занятые интервалы</h2>
                <p class="form-note">Время по Саратову. Показаны до 8 ближайших интервалов.</p>
                <ul>
                    @forelse ($upcomingBookings as $booking)
                        <li>{{ $booking->startsAtInClubTime()->format('d.m.Y H:i') }} — {{ $booking->endsAtInClubTime()->format('d.m.Y H:i') }}</li>
                    @empty
                        <li class="muted">Предстоящих бронирований пока нет.</li>
                    @endforelse
                </ul>
            </section>
        </section>
        <aside class="panel detail-panel">
            @can('update', $computer)
                <p class="eyebrow">УПРАВЛЕНИЕ</p>
                <h2>Настройки компьютера</h2>
                <p class="muted">Можно изменить описание или отключить приём новых броней. Существующие брони при отключении сохраняются.</p>
                <a class="button button-dark" href="{{ route('computers.edit', $computer) }}">Редактировать</a>
                <details class="delete-form">
                    <summary class="button button-danger">Удалить компьютер</summary>
                    <div class="confirmation">
                        <p>Удалить {{ $computer->name }} из каталога? Это действие нельзя отменить.</p>
                        <form action="{{ route('computers.destroy', $computer) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="button button-danger" type="submit">Подтвердить удаление</button>
                        </form>
                    </div>
                </details>
                <p class="form-note">Удаление возможно только при отсутствии бронирований.</p>
            @else
                <p class="eyebrow">ВАШ ВИЗИТ</p>
                <h2>Ваша бронь — под контролем</h2>
                <p class="muted">До начала визита вы можете перенести или отменить бронь в разделе «Мои бронирования».</p>
                @guest
                    <p class="form-note">Для оформления нужна учётная запись. Выбранный компьютер сохранится при переходе ко входу или регистрации.</p>
                @endguest
            @endcan
        </aside>
    </div>
</x-layout>
