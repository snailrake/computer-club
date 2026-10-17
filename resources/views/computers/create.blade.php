<x-layout title="Добавить компьютер">
    <a class="back-link" href="{{ route('computers.index') }}">← Все компьютеры</a>
    <section class="page-heading">
        <div>
            <p class="eyebrow">УПРАВЛЕНИЕ МЕСТАМИ</p>
            <h1>Новый компьютер</h1>
        </div>
    </section>
    <form class="panel form-panel form-stack" action="{{ route('computers.store') }}" method="POST">
        @csrf
        @include('computers._form')
        <div class="form-actions">
            <button class="button button-primary" type="submit">Добавить компьютер</button>
            <a class="button button-quiet" href="{{ route('computers.index') }}">Отмена</a>
        </div>
    </form>
</x-layout>
