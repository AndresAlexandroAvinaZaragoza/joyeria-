@props([
    'paginator',
    'previousLabel' => 'Anterior',
    'nextLabel' => 'Siguiente',
])

@php
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $pages = collect([1, $lastPage]);

    for ($page = max(1, $currentPage - 2); $page <= min($lastPage, $currentPage + 2); $page++) {
        $pages->push($page);
    }

    $pages = $pages->unique()->sort()->values();
@endphp

@if ($paginator->onFirstPage())
    <button type="button" disabled>{{ $previousLabel }}</button>
@else
    <a href="{{ $paginator->previousPageUrl() }}" @click="if (typeof sessionStorage !== 'undefined') sessionStorage.setItem('config-scroll-y', String(window.scrollY))">{{ $previousLabel }}</a>
@endif

@foreach ($pages as $pageIndex => $page)
    @if ($pageIndex > 0 && $page - $pages[$pageIndex - 1] > 1)
        <span class="pagination-window__ellipsis" aria-hidden="true">&hellip;</span>
    @endif

    @if ($page === $currentPage)
        <a href="{{ $paginator->url($page) }}" class="pagina-activa pagination-window__current" aria-current="page" aria-label="Página actual {{ $page }}" @click="if (typeof sessionStorage !== 'undefined') sessionStorage.setItem('config-scroll-y', String(window.scrollY))">{{ $page }}</a>
    @else
        <a href="{{ $paginator->url($page) }}" @click="if (typeof sessionStorage !== 'undefined') sessionStorage.setItem('config-scroll-y', String(window.scrollY))">{{ $page }}</a>
    @endif
@endforeach

@if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" @click="if (typeof sessionStorage !== 'undefined') sessionStorage.setItem('config-scroll-y', String(window.scrollY))">{{ $nextLabel }}</a>
@else
    <button type="button" disabled>{{ $nextLabel }}</button>
@endif
