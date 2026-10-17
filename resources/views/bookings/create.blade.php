<x-layout title="Новая бронь">
    <a class="back-link" href="{{ route('bookings.index') }}">← Бронирования</a>
    <section class="page-heading">
        <div>
            <p class="eyebrow">ЗАПЛАНИРУЙТЕ ВИЗИТ</p>
            <h1>Новое бронирование</h1>
            <p class="muted">Выберите место и время по Саратову. Мы проверим, что период свободен.</p>
        </div>
    </section>
    @if ($selectedComputerId > 0 && ! $computers->contains('id', $selectedComputerId))
        <p class="notice notice-neutral" role="status">Выбранный компьютер больше не принимает бронирования. Выберите другое место.</p>
    @endif
    <div class="detail-grid">
        @if ($computers->isEmpty())
            <section class="panel empty-state">
                <h2>Нет доступных компьютеров</h2>
                <p>Сейчас ни одно место не принимает новые брони.</p>
                <a class="button button-dark" href="{{ route('computers.index') }}">Вернуться к компьютерам</a>
            </section>
        @else
            <form class="panel detail-panel form-stack" action="{{ route('bookings.store') }}" method="POST">
                @csrf
                @include('bookings._form')
                <div class="form-actions">
                    <button class="button button-primary" type="submit">Подтвердить бронирование →</button>
                    <a class="button button-quiet" href="{{ route('bookings.index') }}">Отмена</a>
                </div>
            </form>
        @endif
        <aside class="panel detail-panel booking-help">
            <p class="eyebrow">ПОДТВЕРЖДЕНИЕ БРОНИ</p>
            <h2>Запланируйте визит</h2>
            <p>Бронь оформляется на вашу учётную запись: <strong>{{ auth()->user()->name }}</strong>.</p>
            <hr>
            <p>После подтверждения откроется карточка брони. До начала визита её можно изменить или отменить.</p>
            <p>Если период занят, форма сохранит введённые данные и объяснит, что нужно изменить.</p>
            <p class="muted">Соседние брони разрешены: например, 10:00–11:00 и 11:00–12:00.</p>
        </aside>
    </div>
</x-layout>
