<?php
$hasDelivery = $locationCurrent->hasDelivery();
$hasCollection = $location->current()->hasCollection();
$schedule = $location->workingSchedule($location->orderType());
?>
<dl>
    <?php if ($schedule->isOpen()) { ?>
        <dt><?= lang('igniter.local::default.text_is_opened'); ?></dt>
    <?php }
    else if ($schedule->isOpening()) { ?>
        <dt class="text-muted"><?= sprintf(lang('igniter.local::default.text_opening_time'), $schedule->getOpenTime('D '.$openingTimeFormat)); ?></dt>
    <?php }
    else { ?>
        <dt class="text-muted"><?= lang('igniter.local::default.text_closed'); ?></dt>
    <?php } ?>

    <dd>
        <?php if ($schedule->getPeriod()->opensAllDay()) { ?>
            <span class="fa fa-clock-o"></span>&nbsp;&nbsp;
            <span><?= lang('igniter.local::default.text_24_7_hour'); ?></span>
        <?php }
        else { ?>
            <span class="fa fa-clock-o"></span>&nbsp;
            <span>
                <?= $schedule->getOpenTime($openingTimeFormat); ?>
                -
                <?= $schedule->getCloseTime($openingTimeFormat); ?>
                </span>
        <?php } ?>
    </dd>

    <dd class="text-muted">
        <?php if (!$hasDelivery AND $hasCollection) { ?>
            <?= lang('igniter.local::default.text_collection_only'); ?>
        <?php }
        else if ($hasDelivery AND !$hasCollection) { ?>
            <?= lang('igniter.local::default.text_delivery_only'); ?>
        <?php }
        else if ($hasDelivery AND $hasCollection) { ?>
            <?= lang('igniter.local::default.text_both_types'); ?>
        <?php }
        else { ?>
            <?= lang('igniter.local::default.text_no_types'); ?>
        <?php } ?>
    </dd>
    <dd class="text-muted">
        <?= $__SELF__->deliveryConditionText(); ?>
    </dd>
</dl>
