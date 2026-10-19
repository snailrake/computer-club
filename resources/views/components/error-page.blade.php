@props(['code', 'title', 'detail'])
<x-layout :title="$title">
    <section class="panel">
        <p class="muted">Ошибка {{ $code }}</p>
        <h1>{{ $title }}</h1>
        <p>{{ $detail }}</p>
        <a class="button secondary" href="{{ route('computers.index') }}">К компьютерам</a>
    </section>
</x-layout>
