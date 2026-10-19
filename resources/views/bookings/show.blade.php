<x-layout :title="'Бронь #'.$booking->id">
    <p><a href="{{ route('bookings.index') }}">← Бронирования</a></p>
    <h1>Бронирование #{{ $booking->id }}</h1>
    <section class="panel">
        <dl class="details">
            <dt>Компьютер</dt>
            <dd><a href="{{ route('computers.show', $booking->computer) }}">{{ $booking->computer->name }}</a></dd>
            <dt>Начало</dt>
            <dd>{{ $booking->startsAtInClubTime()->format('d.m.Y · H:i') }}</dd>
            <dt>Окончание</dt>
            <dd>{{ $booking->endsAtInClubTime()->format('d.m.Y · H:i') }}</dd>
            <dt>Клиент</dt>
            <dd>{{ $booking->user->name }}</dd>
        </dl>
        <div class="actions">
            @can('update', $booking)
                <a class="button" href="{{ route('bookings.edit', $booking) }}">Изменить бронирование</a>
            @endcan
            @can('delete', $booking)
                <form action="{{ route('bookings.destroy', $booking) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="danger" type="submit">Удалить бронирование</button>
                </form>
            @endcan
        </div>
    </section>
</x-layout>
