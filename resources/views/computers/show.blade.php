<x-layout :title="$computer->name">
    <p><a href="{{ route('computers.index') }}">← Компьютеры</a></p>
    <h1>{{ $computer->name }}</h1>
    <section class="panel">
        <p class="description">{{ $computer->description ?: 'Описание не добавлено.' }}</p>
        <p>{{ $computer->is_active ? 'Компьютер доступен для бронирования.' : 'Приём бронирований отключён.' }}</p>
        <div class="actions">
            @if ($computer->is_active)
                <a class="button" href="{{ route('bookings.create', ['computer' => $computer->id]) }}">Забронировать компьютер</a>
            @endif
            @can('update', $computer)
                <a class="button secondary" href="{{ route('computers.edit', $computer) }}">Редактировать</a>
            @endcan
            @can('delete', $computer)
                <form action="{{ route('computers.destroy', $computer) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="danger" type="submit">Удалить компьютер</button>
                </form>
            @endcan
        </div>
    </section>
</x-layout>
