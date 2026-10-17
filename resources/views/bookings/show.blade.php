<x-layout :title="'Бронь #'.$booking->id">
    <a class="back-link" href="{{ route('bookings.index') }}">← Бронирования</a>
    <section class="page-heading">
        <div>
            <p class="eyebrow">ВАШ ВИЗИТ В КЛУБ</p>
            <h1>Бронирование #{{ $booking->id }}</h1>
        </div>
        <x-booking-status :booking="$booking" />
    </section>
    <div class="detail-grid">
        <section class="panel detail-panel">
            <p class="eyebrow">КОМПЬЮТЕР</p>
            <h2 class="large-title">
                <a href="{{ route('computers.show', $booking->computer) }}">{{ $booking->computer->name }} ↗</a>
            </h2>
            <dl class="booking-facts">
                <div>
                    <dt>Начало</dt>
                    <dd>{{ $booking->startsAtInClubTime()->format('d.m.Y · H:i') }}</dd>
                </div>
                <div>
                    <dt>Окончание</dt>
                    <dd>{{ $booking->endsAtInClubTime()->format('d.m.Y · H:i') }}</dd>
                </div>
                <div>
                    <dt>Владелец</dt>
                    <dd>{{ $booking->user->name }}</dd>
                </div>
                <div>
                    <dt>Местное время клуба</dt>
                    <dd>Саратов (UTC+4)</dd>
                </div>
            </dl>
        </section>
        <aside class="panel detail-panel">
            @can('update', $booking)
                <h2>Планы изменились?</h2>
                <p class="muted">До начала визита можно выбрать другое время или компьютер. При отмене период снова станет доступен.</p>
                <a class="button button-dark" href="{{ route('bookings.edit', $booking) }}">Изменить бронирование</a>
            @else
                <h2>{{ $booking->ends_at->isFuture() ? 'Время вашего визита' : 'Визит завершён' }}</h2>
                <p class="muted">Начавшееся или прошедшее бронирование нельзя перенести. Оно остаётся в истории визитов.</p>
            @endcan
            @can('delete', $booking)
                <details class="delete-form">
                    <summary class="button button-danger">{{ $booking->starts_at->isFuture() ? 'Отменить бронирование' : 'Удалить запись' }}</summary>
                    <div class="confirmation">
                        <p>Подтвердите {{ $booking->starts_at->isFuture() ? 'отмену брони' : 'удаление записи' }} #{{ $booking->id }}. Восстановить её будет нельзя.</p>
                        <form action="{{ route('bookings.destroy', $booking) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="button button-danger" type="submit">Подтвердить {{ $booking->starts_at->isFuture() ? 'отмену' : 'удаление' }}</button>
                        </form>
                    </div>
                </details>
            @endcan
        </aside>
    </div>
</x-layout>
