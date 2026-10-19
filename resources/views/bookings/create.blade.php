<x-layout title="Новая бронь">
    <h1>Новое бронирование</h1>
    @if ($selectedComputerId > 0 && ! $computers->contains('id', $selectedComputerId))
        <p class="notice">Выбранный компьютер больше не принимает бронирования. Выберите другой.</p>
    @endif
    @if ($computers->isEmpty())
        <div class="panel">
            <h2>Нет доступных компьютеров</h2>
            <p>Сейчас ни один компьютер не принимает бронирования.</p>
            <a href="{{ route('computers.index') }}">К компьютерам</a>
        </div>
    @else
        <form class="panel form-panel" action="{{ route('bookings.store') }}" method="POST">
            @csrf
            @include('bookings._form')
            <div class="actions">
                <button type="submit">Подтвердить бронирование</button>
                <a class="button secondary" href="{{ route('bookings.index') }}">Отмена</a>
            </div>
        </form>
    @endif
</x-layout>
