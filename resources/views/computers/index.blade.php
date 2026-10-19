<x-layout title="Компьютеры">
    <div class="page-heading">
        <h1>Компьютеры</h1>
        @can('create', App\Models\Computer::class)
            <a class="button" href="{{ route('computers.create') }}">Добавить компьютер</a>
        @endcan
    </div>
    <div class="panel table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Приём бронирований</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($computers as $computer)
                    <tr>
                        <td>{{ $computer->name }}</td>
                        <td>{{ $computer->is_active ? 'Доступен' : 'Отключён' }}</td>
                        <td><a href="{{ route('computers.show', $computer) }}">Открыть</a></td>
                    </tr>
                @empty
                    <tr><td colspan="3">Компьютеров пока нет.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $computers->links('components.pagination') }}
</x-layout>
