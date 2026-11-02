@if($paginator->hasPages())
<div class="sn-pagination">
    <div class="sn-pagination-summary">
        Showing <strong>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}</strong>
        of <strong>{{ $paginator->total() }}</strong> entries
    </div>
    <nav class="sn-page-links" aria-label="Pagination">
        @if($paginator->onFirstPage())
            <span class="sn-page-link disabled">Previous</span>
        @else
            <a class="sn-page-link" href="{{ $paginator->previousPageUrl() }}">Previous</a>
        @endif
        @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
            @if($page == 1 || $page == $paginator->lastPage() || abs($page - $paginator->currentPage()) <= 1)
                <a class="sn-page-link {{ $paginator->currentPage() == $page ? 'active' : '' }}" href="{{ $url }}">{{ $page }}</a>
            @elseif($page == 2 && $paginator->currentPage() > 3)
                <span class="sn-page-ellipsis">…</span>
            @elseif($page == $paginator->lastPage() - 1 && $paginator->currentPage() < $paginator->lastPage() - 2)
                <span class="sn-page-ellipsis">…</span>
            @endif
        @endforeach
        @if($paginator->hasMorePages())
            <a class="sn-page-link" href="{{ $paginator->nextPageUrl() }}">Next</a>
        @else
            <span class="sn-page-link disabled">Next</span>
        @endif
    </nav>
</div>
@endif
<style>
.sn-pagination{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;padding:14px 18px;border-top:1px solid #E2E8F0;font-family:Inter,sans-serif}
.sn-pagination-summary{font-size:12px;color:#64748B}.sn-pagination-summary strong{color:#25313C;font-weight:600}
.sn-page-links{display:flex;align-items:center;gap:5px;flex-wrap:wrap}
.sn-page-link{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;border:1px solid #CBD5E1;border-radius:8px;background:#fff;color:#123B5D;font:600 12px Inter,sans-serif;text-decoration:none}
.sn-page-link:hover{background:#F8FAFC;color:#123B5D}.sn-page-link.active{background:#123B5D;border-color:#123B5D;color:#fff}
.sn-page-link.disabled{background:#F8FAFC;color:#94A3B8;border-color:#E2E8F0}.sn-page-ellipsis{padding:0 4px;color:#64748B}
</style>
