@props(['booking'])
@if ($booking->starts_at->isFuture())
    <span class="badge badge-green">Предстоящая</span>
@elseif ($booking->ends_at->isFuture())
    <span class="badge badge-green">Время визита</span>
@else
    <span class="badge badge-muted">Завершена</span>
@endif
