<form
    id="location-search"
    method="POST"
    role="form"
    data-request="<?= $searchEventHandler; ?>"
>
    <div class="input-group">
        <input
            type="text"
            id="search-query"
            class="form-control text-center"
            name="search_query"
            placeholder="<?= lang('igniter.local::default.label_search_query'); ?>"
            value="<?= $location->userPosition()->isValid() ? $location->userPosition()->format() : ''; ?>"
        >
        <div class="input-group-prepend">
            <button
                type="button"
                class="btn btn-light"
                data-control="search-local"
            ><?= lang('igniter.local::default.button_search_location'); ?></button>
        </div>
    </div>
</form>
