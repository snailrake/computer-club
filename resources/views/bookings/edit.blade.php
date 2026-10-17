<x-layout title="Изменить бронь">
    <a class="back-link" href="{{ route('bookings.show', $booking) }}">← Бронь #{{ $booking->id }}</a>
    <section class="page-heading">
        <div>
            <p class="eyebrow">ИЗМЕНЕНИЕ ПЛАНОВ</p>
            <h1>Перенести бронирование</h1>
            <p class="muted">Выберите новый компьютер или период по Саратову. При ошибке исходная бронь сохранится.</p>
            @if (auth()->user()->isAdmin() && $booking->user_id !== auth()->id())
                <p class="form-note">Бронь клиента: {{ $booking->user->name }}. Владелец останется прежним.</p>
            @endif
        </div>
    </section>
    @if ($computers->isEmpty())
        <section class="panel empty-state">
            <h2>Нет компьютеров для переноса</h2>
            <p>Сейчас ни одно место не принимает брони. Существующая бронь сохранена.</p>
            <a class="button button-dark" href="{{ route('bookings.show', $booking) }}">К бронированию</a>
        </section>
    @else
        <form class="panel form-panel form-stack" action="{{ route('bookings.update', $booking) }}" method="POST">
            @csrf
            @method('PATCH')
            @include('bookings._form')
            <div class="form-actions">
                <button class="button button-primary" type="submit">Сохранить изменения</button>
                <a class="button button-quiet" href="{{ route('bookings.show', $booking) }}">Отмена</a>
            </div>
        </form>
    @endif
</x-layout>
