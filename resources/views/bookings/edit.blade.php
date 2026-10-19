<x-layout title="Изменить бронь">
    <h1>Изменить бронирование #{{ $booking->id }}</h1>
    @if ($computers->isEmpty())
        <div class="panel">
            <h2>Нет компьютеров для переноса</h2>
            <p>Существующая бронь сохранена.</p>
            <a href="{{ route('bookings.show', $booking) }}">К бронированию</a>
        </div>
    @else
        <form class="panel form-panel" action="{{ route('bookings.update', $booking) }}" method="POST">
            @csrf
            @method('PATCH')
            @include('bookings._form')
            <div class="actions">
                <button type="submit">Сохранить изменения</button>
                <a class="button secondary" href="{{ route('bookings.show', $booking) }}">Отмена</a>
            </div>
        </form>
    @endif
</x-layout>
