@props(['paginator'])

@if($paginator->hasPages())
    <div class="pagination-wrapper">
        {{-- Previous --}}
        @if($paginator->onFirstPage())
            <span class="page-link disabled">←</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="page-link">←</a>
        @endif

        {{-- Pages --}}
        @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if($page == $paginator->currentPage())
                <span class="page-link active">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="page-link">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Next --}}
        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="page-link">→</a>
        @else
            <span class="page-link disabled">→</span>
        @endif
    </div>
@endif
