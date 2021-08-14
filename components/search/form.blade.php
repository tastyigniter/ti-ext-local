<form
    id="location-search"
    method="POST"
    role="form"
    data-request="{{ $searchEventHandler }}"
>
    <div class="input-group postcode-group">
        <input
            type="text"
            id="search-query"
            class="form-control text-center postcode-control"
            name="search_query"
            placeholder="@lang('igniter.local::default.label_search_query')"
            value="{{ $__SELF__->getSearchQuery() }}"
        >
        <div class="input-group-append">
            <button
                type="button"
                class="btn btn-primary"
                data-control="search-local"
            >@lang('igniter.local::default.text_find')</button>
        </div>
    </div>
</form>
