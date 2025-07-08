<!--begin::Pagination-->
<div class="d-flex flex-stack flex-wrap pb-5">
    @if ($paginator->hasPages())
        <div class="fs-6 fw-semibold text-gray-700">@lang('admin.general.show_in_pagination', ['from' => (($paginator->currentPage() - 1) * $paginator->perPage() + 1) , 'to' => $paginator->currentPage() * $paginator->perPage()	, 'total' => $paginator->total()])</div>
        <!--begin::Pages-->
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item previous">
                    <i class="previous"></i>
                </li>
            @else
                <li class="page-item previous">
                    <a data-kt-pagination="page-link" href="{{ $paginator->previousPageUrl() }}" class="page-link">
                        <i class="previous"></i>
                    </a>
                </li>
            @endif
            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item disabled">{{ $element }}</li>
                @endif
                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a data-kt-pagination="page-link" href="{{ $url }}" class="page-link">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach
            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item next">

                    <a class="page-link" data-kt-pagination="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="next"></i></a>
                </li>
            @else
                <li class="page-item next disabled">
                    <i class="next"></i>
                </li>
            @endif
            {{-- @foreach ($links as $link)
        <li class="page-item {{ $link->active ? 'active' : '' }}">
            <a href="{{ $link->url ? $link->url : '' }}" class="page-link">{{ is_numeric($link->label) ? $link->label : __('admin.general.'.$link->label) }}</a>
        </li>
        @endforeach --}}

        </ul>
    @endif
    <!--end::Pages-->
</div>
<!--end::Pagination-->
