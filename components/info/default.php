<div class="panel">
    <?php if ('' !== ($description = $currentLocation->getDescription())) { ?>
        <div class="panel-body">
            <h1
                class="h4 wrap-bottom border-bottom"
            ><?= sprintf(lang('igniter.local::default.text_info_heading'), $currentLocation->getName()); ?></h1>
            <p class="m-0"><?= $description; ?></p>
        </div>
    <?php } ?>

    <div class="list-group list-group-flush">
        <?php if (!empty($openingType) AND $openingType == '24_7') { ?>
            <div class="list-group-item"><?= lang('igniter.local::default.text_opens_24_7'); ?></div>
        <?php } ?>
        <?php if ($hasDelivery) { ?>
            <div class="list-group-item">
                <?= lang('igniter.local::default.text_delivery'); ?>
                <?php if ($deliverySchedule->isOpen()) { ?>
                    <?= sprintf(lang('igniter.local::default.text_in_minutes'), $deliveryTime); ?>
                <?php } else if ($deliverySchedule->isOpening()) { ?>
                    <?= sprintf(lang('igniter.local::default.text_starts'), $deliverySchedule->getOpenTime('H:i a')); ?>
                <?php } else { ?>
                    <?= lang('igniter.local::default.text_closed'); ?>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if ($hasCollection) { ?>
            <div class="list-group-item">
                <?= lang('igniter.local::default.text_collection'); ?>
                <?php if ($collectionSchedule->isOpen()) { ?>
                    <?= sprintf(lang('igniter.local::default.text_in_minutes'), $collectionTime); ?>
                <?php } else if ($collectionSchedule->isOpening()) { ?>
                    <?= sprintf(lang('igniter.local::default.text_starts'), $collectionSchedule->getOpenTime('H:i a')); ?>
                <?php } else { ?>
                    <?= lang('igniter.local::default.text_closed'); ?>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if ($hasDelivery) { ?>
            <div class="list-group-item">
                <?= lang('igniter.local::default.text_last_order_time'); ?>&nbsp;
                <?= $lastOrderTime; ?>
            </div>
        <?php } ?>
        <?php if ($localPayments->isNotEmpty()) { ?>
            <div class="list-group-item">
                <i class="fa fa-paypal fa-fw"></i>&nbsp;<b><?= lang('igniter.local::default.text_payments'); ?></b><br/>
                <?= implode(', ', $localPayments->pluck('name')->all()); ?>.
            </div>
        <?php } ?>
    </div>

    <?= partial('@areas') ?>

    <h4 class="panel-title p-3"><b><?= lang('igniter.local::default.text_hours'); ?></b></h4>
    <?= partial('@hours') ?>
</div>

