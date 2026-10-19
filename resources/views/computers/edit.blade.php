<x-layout title="Изменить компьютер">
    <h1>Изменить компьютер</h1>
    <form class="panel form-panel" action="{{ route('computers.update', $computer) }}" method="POST">
        @csrf
        @method('PATCH')
        @include('computers._form')
        <div class="actions">
            <button type="submit">Сохранить изменения</button>
            <a class="button secondary" href="{{ route('computers.show', $computer) }}">Отмена</a>
        </div>
    </form>
</x-layout>
