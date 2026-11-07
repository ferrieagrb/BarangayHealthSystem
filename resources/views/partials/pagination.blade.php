@if ($paginator->hasPages())
    @php
        $numbered = method_exists($paginator, 'lastPage');
        $current = method_exists($paginator, 'currentPage') ? $paginator->currentPage() : null;
        $pages = collect();
        if ($numbered) {
            $last = $paginator->lastPage();
            $window = min($last, max(1, $current));
            $start = max(1, $window - 1);
            $end = min($last, $window + 1);
            if ($window <= 3) $end = min($last, max(3, $end));
            if ($window >= $last - 2) $start = max(1, min($last - 2, $start));
            $pages = collect([1, $last])->merge(range($start, $end))->unique()->sort()->values();
        }
        $previousPage = null;
    @endphp
    <nav class="bheams-pagination" aria-label="Pagination navigation">
        <p class="bheams-pagination-summary">
            @if ($numbered)
                Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} results
            @elseif ($current)
                Page {{ $current }}
            @else
                More results
            @endif
        </p>
        <div class="bheams-pagination-scroll" data-pagination-scroll tabindex="0" aria-label="Page links, scroll horizontally for more">
            <ul class="bheams-page-list">
                <li>
                    @if ($paginator->onFirstPage())
                        <span class="bheams-page-link is-disabled" aria-disabled="true" aria-label="Previous page">&lsaquo;</span>
                    @else
                        <a class="bheams-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">&lsaquo;</a>
                    @endif
                </li>
                @foreach ($pages as $page)
                    @if ($previousPage !== null && $page > $previousPage + 1)
                        <li><span class="bheams-page-link is-gap" aria-hidden="true">&hellip;</span></li>
                    @endif
                    <li>
                        @if ($page === $current)
                            <span class="bheams-page-link is-current" aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>
                        @else
                            <a class="bheams-page-link" href="{{ $paginator->url($page) }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                        @endif
                    </li>
                    @php $previousPage = $page; @endphp
                @endforeach
                <li>
                    @if ($paginator->hasMorePages())
                        <a class="bheams-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">&rsaquo;</a>
                    @else
                        <span class="bheams-page-link is-disabled" aria-disabled="true" aria-label="Next page">&rsaquo;</span>
                    @endif
                </li>
            </ul>
        </div>
    </nav>
@endif
