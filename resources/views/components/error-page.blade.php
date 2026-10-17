@props(['code', 'title', 'detail'])
<x-layout :title="$title">
    <section class="panel empty-state error-page">
        <p class="eyebrow">{{ $code }}</p>
        <h1>{{ $title }}</h1>
        <p>{{ $detail }}</p>
        <a class="button button-dark" href="{{ route('computers.index') }}">К компьютерам</a>
    </section>
</x-layout>
