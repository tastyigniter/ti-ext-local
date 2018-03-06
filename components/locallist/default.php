<?php if (count($locationsList)) { ?>
    <?= partial('@list', [
        'locationsList'    => $locationsList,
        'distanceUnit' => $distanceUnit,
    ]); ?>

    <div class="pagination-bar text-right">
        <div class="links"><?= $locationsList->links(); ?></div>
    </div>
<?php }
else { ?>
    <div class="panel panel-local">
        <div class="panel-body">
            <p><?= lang('sampoyigi.local::default.text_filter_no_match'); ?></p>
        </div>
    </div>
<?php } ?>
