<x-layout title="Редактировать компьютер">
    <a class="back-link" href="{{ route('computers.show', $computer) }}">← {{ $computer->name }}</a>
    <section class="page-heading">
        <div>
            <p class="eyebrow">УПРАВЛЕНИЕ МЕСТАМИ</p>
            <h1>Редактирование компьютера</h1>
        </div>
    </section>
    <form class="panel form-panel form-stack" action="{{ route('computers.update', $computer) }}" method="POST">
        @csrf
        @method('PATCH')
        @include('computers._form')
        <div class="form-actions">
            <button class="button button-primary" type="submit">Сохранить изменения</button>
            <a class="button button-quiet" href="{{ route('computers.show', $computer) }}">Отмена</a>
        </div>
    </form>
</x-layout>
