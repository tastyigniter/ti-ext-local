<?php if ('' !== ($description = $currentLocation->getDescription())) { ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <h4
                class="wrap-bottom border-bottom"
            ><?= sprintf(lang('sampoyigi.local::default.text_info_heading'), $currentLocation->getName()); ?></h4>
            <p><?= $description; ?></p>
        </div>
    </div>
<?php } ?>


<div class="row wrap-bottom">
    <div class="col-sm-12">
        <div class="list-group">
            <?php if (!empty($openingType) AND $openingType == '24_7') { ?>
                <div class="list-group-item"><?= lang('sampoyigi.local::default.text_opens_24_7'); ?></div>
            <?php } ?>
            <?php if ($hasDelivery) { ?>
                <div class="list-group-item">
                    <?= lang('sampoyigi.local::default.text_delivery'); ?>
                    <?php if ($deliverySchedule->isOpen()) { ?>
                        <?= sprintf(lang('sampoyigi.local::default.text_in_minutes'), $deliveryTime); ?>
                    <?php } else if ($deliverySchedule->isOpening()) { ?>
                        <?= sprintf(lang('sampoyigi.local::default.text_starts'), $deliverySchedule->getOpenTime('H:i a')); ?>
                    <?php } else { ?>
                        <?= lang('sampoyigi.local::default.text_closed'); ?>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if ($hasCollection) { ?>
                <div class="list-group-item">
                    <?= lang('sampoyigi.local::default.text_collection'); ?>
                    <?php if ($collectionSchedule->isOpen()) { ?>
                        <?= sprintf(lang('sampoyigi.local::default.text_in_minutes'), $collectionTime); ?>
                    <?php } else if ($collectionSchedule->isOpening()) { ?>
                        <?= sprintf(lang('sampoyigi.local::default.text_starts'), $collectionSchedule->getOpenTime('H:i a')); ?>
                    <?php } else { ?>
                        <?= lang('sampoyigi.local::default.text_closed'); ?>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if ($hasDelivery) { ?>
                <div class="list-group-item">
                    <?= lang('sampoyigi.local::default.text_last_order_time'); ?>&nbsp;
                    <?= $lastOrderTime; ?>
                </div>
            <?php } ?>
            <?php if ($localPayments->isNotEmpty()) { ?>
                <div class="list-group-item">
                    <i class="fa fa-paypal fa-fw"></i>&nbsp;<b><?= lang('sampoyigi.local::default.text_payments'); ?></b><br/>
                    <?= implode(', ', $localPayments->pluck('name')->all()); ?>.
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <?= partial('@areas') ?>
    </div>

    <div class="col-sm-12">
        <h4><b><?= lang('sampoyigi.local::default.text_hours'); ?></b></h4>
        <?= partial('@hours') ?>
    </div>
</div>

