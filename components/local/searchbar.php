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
            placeholder="<?= lang('sampoyigi.local::default.label_search_query'); ?>"
            value="<?= $userPosition->formattedAddress; ?>"
        >
        <div class="input-group-prepend">
            <button
                type="button"
                class="btn btn-light"
                data-control="search-local"
            ><?= lang('sampoyigi.local::default.button_search_location'); ?></button>
        </div>
    </div>
</form>
