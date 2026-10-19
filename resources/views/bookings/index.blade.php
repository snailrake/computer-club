<x-layout title="Бронирования">
    <div class="page-heading">
        <h1>{{ auth()->user()->isAdmin() ? 'Все бронирования' : 'Мои бронирования' }}</h1>
        <a class="button" href="{{ route('bookings.create') }}">Новое бронирование</a>
    </div>
    <div class="panel table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Компьютер</th>
                    <th>Начало</th>
                    <th>Окончание</th>
                    @if (auth()->user()->isAdmin())
                        <th>Клиент</th>
                    @endif
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                    <tr>
                        <td>{{ $booking->computer->name }}</td>
                        <td>{{ $booking->startsAtInClubTime()->format('d.m.Y H:i') }}</td>
                        <td>{{ $booking->endsAtInClubTime()->format('d.m.Y H:i') }}</td>
                        @if (auth()->user()->isAdmin())
                            <td>{{ $booking->user->name }}</td>
                        @endif
                        <td><a href="{{ route('bookings.show', $booking) }}">Открыть</a></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->isAdmin() ? 5 : 4 }}">Бронирований пока нет.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $bookings->links('components.pagination') }}
</x-layout>
