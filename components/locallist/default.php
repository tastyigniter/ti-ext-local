<div class="row">
    <div class="locations-filter col-sm-3">
        <?= partial('@filter'); ?>
    </div>
    <div class="col-sm-9">
        <?php if ($locations) { ?>
            <?= partial('@list', [
                'locations'    => $locations,
                'distanceUnit' => $distanceUnit,
            ]); ?>
        <?php }
        else { ?>
            <div class="panel panel-local">
                <div class="panel-body">
                    <p><?= lang('sampoyigi.local::default.text_filter_no_match'); ?></p>
                </div>
            </div>
        <?php } ?>
    </div>
</div>