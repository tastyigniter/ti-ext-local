<div id="local-box">
    <div class="panel local-search">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-4">
                    <div
                        id="local-timeslot"
                        data-control="timepicker"
                        data-time-slot="<?= e(json_encode($orderTimeSlot)); ?>"
                    >
                        <?= partial('@timeslot'); ?>
                    </div>
                </div>
                <div class="col-sm-8">
                    <?= partial('@searchbar'); ?>
                </div>
            </div>
            <?php if ($requiresUserPosition AND $userPosition->isValid() AND !$userPositionIsCovered) { ?>
                <span class="help-block"><?= lang('igniter.local::default.text_delivery_coverage'); ?></span>
            <?php } ?>
        </div>
    </div>

    <?php if ($currentLocation) { ?>
        <div class="panel panel-local">
            <div class="panel-body">
                <div class="row boxes">
                    <div class="box-one col-sm-6">
                        <?= partial('@box_one'); ?>
                    </div>
                    <div class="box-divider d-block d-sm-none"></div>
                    <div class="box-two col-sm-6">
                        <?= partial('@box_two'); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
