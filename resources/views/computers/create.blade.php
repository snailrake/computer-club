<x-layout title="Новый компьютер">
    <h1>Новый компьютер</h1>
    <form class="panel form-panel" action="{{ route('computers.store') }}" method="POST">
        @csrf
        @include('computers._form')
        <div class="actions">
            <button type="submit">Добавить компьютер</button>
            <a class="button secondary" href="{{ route('computers.index') }}">Отмена</a>
        </div>
    </form>
</x-layout>
