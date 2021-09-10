<div id="local-box" class="local-box-fluid">
    @if ($hideSearch)
        <div class="panel panel-local local-search">
            <div class="panel-body">
                <a
                    class="btn btn-block btn-primary"
                    href="{{ restaurant_url($menusPage) }}"
                >@lang('igniter.local::default.text_find')</a>
            </div>
        </div>
    @else
        <h2 class="text-center text-primary">@lang('igniter.local::default.text_order_summary')</h2>
        <span class="search-label sr-only">@lang('igniter.local::default.label_search_query')</span>
        <div id="local-search-container">
            @partial('@container')
        </div>
    @endif
</div>
