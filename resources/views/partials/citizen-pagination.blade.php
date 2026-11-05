@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $pages = collect([1, $current, $last]);
        if ($current <= 2) {
            $pages = $pages->merge([2, 3]);
        } elseif ($current >= $last - 1) {
            $pages = $pages->merge([$last - 2, $last - 1]);
        }
        $pages = $pages->filter(fn ($page) => $page >= 1 && $page <= $last)->unique()->sort()->values();
        $previousPage = null;
    @endphp
    <nav class="citizen-pagination-nav" aria-label="Citizen records pagination">
        <p class="citizen-pagination-summary">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        </p>
        <div class="citizen-pagination-scroll">
            <ul class="citizen-page-list">
                <li>
                    @if ($paginator->onFirstPage())
                        <span class="citizen-page-link is-disabled" aria-disabled="true" aria-label="Previous page">&lsaquo;</span>
                    @else
                        <a class="citizen-page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">&lsaquo;</a>
                    @endif
                </li>
                @foreach ($pages as $page)
                    @if ($previousPage !== null && $page > $previousPage + 1)
                        <li><span class="citizen-page-link citizen-page-gap" aria-hidden="true">&hellip;</span></li>
                    @endif
                    <li>
                        @if ($page === $current)
                            <span class="citizen-page-link is-current" aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>
                        @else
                            <a class="citizen-page-link" href="{{ $paginator->url($page) }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                        @endif
                    </li>
                    @php $previousPage = $page; @endphp
                @endforeach
                <li>
                    @if ($paginator->hasMorePages())
                        <a class="citizen-page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">&rsaquo;</a>
                    @else
                        <span class="citizen-page-link is-disabled" aria-disabled="true" aria-label="Next page">&rsaquo;</span>
                    @endif
                </li>
            </ul>
        </div>
    </nav>
@endif
