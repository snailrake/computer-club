@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Страницы списка">
        @if ($paginator->onFirstPage())
            <span class="muted">← Назад</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}">← Назад</a>
        @endif
        <span>Страница {{ $paginator->currentPage() }} из {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">Вперёд →</a>
        @else
            <span class="muted">Вперёд →</span>
        @endif
    </nav>
@endif
