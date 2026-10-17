@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Страницы списка">
        <div>
            @if ($paginator->onFirstPage())
                <span class="muted">← Назад</span>
            @else
                <a class="text-link" href="{{ $paginator->previousPageUrl() }}">← Назад</a>
            @endif
        </div>
        <span>Страница {{ $paginator->currentPage() }} из {{ $paginator->lastPage() }}</span>
        <div>
            @if ($paginator->hasMorePages())
                <a class="text-link" href="{{ $paginator->nextPageUrl() }}">Вперёд →</a>
            @else
                <span class="muted">Вперёд →</span>
            @endif
        </div>
    </nav>
@endif
