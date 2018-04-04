<div
    class="row local-search bg-warning"
    style="display: <?= (!$userPosition->isValid() AND $requiresUserPosition) ? 'block' : 'none'; ?>">
    <a class="close-search clickable" onclick="$('.local-change, .local-search').slideToggle()">&times;</a>
    <div class="col-xs-12 col-sm-6 center-block">
        <form
            id="location-search"
            method="POST"
            role="form"
            data-request="<?= $searchEventHandler; ?>"
        >
            <div class="postcode-group text-center">
                <?= lang('sampoyigi.local::default.text_no_search_query'); ?>&nbsp;&nbsp;&nbsp;&nbsp;
                <div class="input-group">
                    <input
                        type="text"
                        id="search-query"
                        class="form-control text-center postcode-control input-xs"
                        name="search_query"
                        placeholder="<?= lang('sampoyigi.local::default.label_search_query'); ?>"
                        value="<?= $userPosition->formattedAddress; ?>">
                    <a
                        id="search"
                        class="input-group-addon btn btn-primary"
                        data-control="search-local"
                    >
                        <?= lang('sampoyigi.local::default.button_search_location'); ?>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div
    class="row local-change"
    style="display: <?= ($userPosition->isValid() OR !$requiresUserPosition) ? 'block' : 'none'; ?>"
>
    <div class="col-xs-12">
        <?= !$userPosition->isValid() ? lang('sampoyigi.local::default.text_enter_location') : sprintf(lang(
            ($userPosition->isValid() AND $userPositionIsCovered)
                ? 'sampoyigi.local::default.text_location_summary'
                : 'sampoyigi.local::default.text_delivery_coverage'
        ), lang('sampoyigi.local::default.text_at').$userPosition->formattedAddress); ?>&nbsp;&nbsp;
        <a
            class="clickable btn-link"
            title=""
            onclick="$('.local-change, .local-search').slideToggle()"
        >
            <?= lang(!$userPosition->isValid() ? 'sampoyigi.local::default.button_enter_location' : 'sampoyigi.local::default.button_change_location'); ?>
        </a>
    </div>
</div>
