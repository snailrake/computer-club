<x-layout title="Бронирования">
    <section class="page-heading">
        <div>
            <p class="eyebrow">ВАШЕ ВРЕМЯ В КЛУБЕ</p>
            <h1>{{ auth()->user()->isAdmin() ? 'Все бронирования' : 'Мои бронирования' }}</h1>
            <p class="muted">{{ auth()->user()->isAdmin() ? 'Просмотр и управление бронированиями всех пользователей.' : 'Переносите и отменяйте свои брони до начала визита.' }} Время — по Саратову.</p>
        </div>
        <a class="button button-primary" href="{{ route('bookings.create') }}">+ Новая бронь</a>
    </section>
    @if ($bookings->isEmpty())
        <section class="empty-state panel">
            <div class="empty-symbol" aria-hidden="true">↗</div>
            <h2>Пока ни одной брони</h2>
            <p>Выберите компьютер и запланируйте свой следующий визит.</p>
            <a class="button button-dark" href="{{ route('bookings.create') }}">Оформить бронирование</a>
        </section>
    @else
        <div class="panel table-wrap">
            <table>
                <caption class="sr-only">Бронирования. Все даты и время — по Саратову.</caption>
                <thead>
                    <tr>
                        <th>Бронь / компьютер</th>
                        <th>Начало</th>
                        <th>Окончание</th>
                        @if (auth()->user()->isAdmin())
                            <th>Пользователь</th>
                        @endif
                        <th>
                            <span class="sr-only">Открыть</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
                        <tr>
                            <td data-label="Компьютер">
                                <strong>{{ $booking->computer->name }}</strong>
                                <span class="table-sub">Бронь #{{ $booking->id }}</span>
                                <x-booking-status :booking="$booking" />
                            </td>
                            <td data-label="Начало">{{ $booking->startsAtInClubTime()->format('d.m.Y') }}<span class="table-time">{{ $booking->startsAtInClubTime()->format('H:i') }}</span>
                            </td>
                            <td data-label="Окончание">{{ $booking->endsAtInClubTime()->format('d.m.Y') }}<span class="table-time">{{ $booking->endsAtInClubTime()->format('H:i') }}</span>
                            </td>
                            @if (auth()->user()->isAdmin())
                                <td data-label="Клиент">{{ $booking->user->name }}<span class="table-sub">{{ $booking->user->email }}</span>
                                </td>
                            @endif
                            <td>
                                <a class="text-link" href="{{ route('bookings.show', $booking) }}" aria-label="Открыть бронь №{{ $booking->id }}">Открыть бронь ↗</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    {{ $bookings->links('components.pagination') }}
</x-layout>
