<form
    id="location-search"
    method="POST"
    role="form"
    data-request="{{ $searchEventHandler }}"
>
    <div class="input-group postcode-group">
        <div class="input-group-prepend">
            <span
                class="input-group-text"
                @if ($searchDefaultAddress)
                role="button"
                data-address-picker-control="new"
                @endif
            ><i class="fa fa-map-marker"></i></span>
        </div>
        <input
            type="text"
            id="search-query"
            class="form-control text-center"
            name="search_query"
            placeholder="@lang('igniter.local::default.label_search_query')"
            value="{{ $__SELF__->getSearchQuery() }}"
        >
        <div class="input-group-append">
            <button
                type="button"
                class="btn btn-light"
                data-control="search-local"
                data-replace-loading="fa fa-spinner fa-spin"
            ><i class="fa fa-check"></i></button>
        </div>
    </div>
</form>
