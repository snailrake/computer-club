<x-layout title="Компьютеры">
    <section class="catalog-intro">
        <div>
            <p class="eyebrow">САРАТОВ · ВАШЕ ПРОСТРАНСТВО ДЛЯ ИГРЫ</p>
            <h1>Место для вашей<br><span>следующей игры.</span></h1>
            <p class="lead">Выберите компьютер, укажите удобное время и приходите играть. Бронирование подтверждается сразу, если выбранный период свободен.</p>
            <ol class="booking-steps" aria-label="Как забронировать">
                <li><span>01</span> Выберите компьютер</li>
                <li><span>02</span> Укажите время</li>
                <li><span>03</span> Подтвердите бронь</li>
            </ol>
        </div>
        @can('create', App\Models\Computer::class)
            <div class="intro-action">
                <p class="eyebrow">УПРАВЛЕНИЕ КЛУБОМ</p>
                <a class="button button-dark" href="{{ route('computers.create') }}">+ Добавить компьютер</a>
                <a class="text-link" href="{{ route('bookings.index') }}">Все бронирования →</a>
            </div>
        @endcan
    </section>
    <div class="section-line">
        <span>КОМПЬЮТЕРЫ КЛУБА</span>
        <span>Всего: {{ $computers->total() }}</span>
    </div>
    <div class="computer-grid">
        @forelse ($computers as $computer)
            <article class="panel computer-card">
                <div class="card-top">
                    <span class="badge {{ $computer->is_active ? 'badge-green' : 'badge-muted' }}">
                        <span class="status-dot"></span>{{ $computer->is_active ? 'Принимает брони' : 'Брони отключены' }}
                    </span>
                    <span class="card-number">#{{ str_pad((string) $computer->id, 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="computer-art" aria-hidden="true">
                    <svg viewBox="0 0 240 130" fill="none">
                        <rect x="33" y="14" width="160" height="94" rx="7" stroke="currentColor" stroke-width="3"/>
                        <path d="M40 97h146M113 108v12m-24 0h48" stroke="currentColor" stroke-width="3"/>
                        <path d="m100 43-18 13 18 13m26-26 18 13-18 13m-12-31-8 48" stroke="currentColor" stroke-width="3"/>
                        <rect x="204" y="41" width="23" height="79" rx="4" stroke="currentColor" stroke-width="3"/>
                        <circle cx="215.5" cy="55" r="3" fill="currentColor"/>
                    </svg>
                </div>
                <h2>{{ $computer->name }}</h2>
                <p class="computer-description">{{ $computer->description ?: 'Описание пока не добавлено.' }}</p>
                <a class="card-link" href="{{ route('computers.show', $computer) }}" aria-label="{{ $computer->name }}: характеристики и бронирование">
                    Характеристики и бронь <span>↗</span>
                </a>
            </article>
        @empty
            <div class="empty-state panel">
                <h2>Компьютеров пока нет</h2>
                <p>Игровые места появятся здесь, когда клуб добавит их в каталог.</p>
            </div>
        @endforelse
    </div>
    {{ $computers->links('components.pagination') }}
</x-layout>
