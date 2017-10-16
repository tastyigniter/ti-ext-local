<div
    class="row local-search bg-warning"
    style="display: <?= $searchQueryRequired ? 'block' : 'none'; ?>">
    <a class="close-search clickable" onclick="$('.local-change, .local-search').slideToggle()">&times;</a>
    <div class="col-xs-12 col-sm-6 center-block">
        <form
            id="location-form"
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
                        value="<?= $searchQuery; ?>">
                    <a
                        id="search"
                        class="input-group-addon btn btn-primary"
                        data-control="search-local"
                    >
                        <?= lang('button_search_location'); ?>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div
    class="row local-change"
    style="display: <?= ($hasSearchQuery OR !$searchQueryRequired) ? 'block' : 'none'; ?>"
>
    <div class="col-xs-12 col-sm-7">
        <?= !$hasSearchQuery ? lang('sampoyigi.local::default.text_enter_location') : sprintf(lang(
            ($hasSearchQuery AND $canDelivery) ? 'sampoyigi.local::default.text_location_summary' : 'sampoyigi.local::default.text_delivery_coverage'
        ), lang('sampoyigi.local::default.text_at').$searchQuery->formatted); ?>&nbsp;&nbsp;
        <a
            class="clickable btn-link"
            title=""
            onclick="$('.local-change, .local-search').slideToggle()"
        >
            <?= lang(!$hasSearchQuery ? 'button_enter_location' : 'button_change_location'); ?>
        </a>
    </div>

    <?php if ($showMenuButton) { ?>
        <div class="col-xs-12 col-sm-5 text-right">
            <a
                class="btn btn-primary btn-menus"
                href="<?= $localMenuUrl; ?>">
                <i class="fa fa-cutlery"></i>
                <span>&nbsp;&nbsp;<?= lang('sampoyigi.local::default.text_goto_menus'); ?></span>
            </a>
        </div>
    <?php } ?>
</div>
