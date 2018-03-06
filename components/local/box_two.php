<dl>
    <?php if ($openingStatus == 'open') { ?>
        <dt><?= lang('sampoyigi.local::default.text_is_opened'); ?></dt>
    <?php }
    else if ($openingStatus == 'opening') { ?>
        <dt class="text-muted"><?= sprintf(lang('sampoyigi.local::default.text_opening_time'), $openingTime); ?></dt>
    <?php }
    else { ?>
        <dt class="text-muted"><?= lang('sampoyigi.local::default.text_closed'); ?></dt>
    <?php } ?>

    <?php if ($openingStatus != 'closed') { ?>
        <dd class="visible-xs">
            <?php if (!empty($openingType) AND $openingType == '24_7') { ?>
                <span class="fa fa-clock-o"></span>&nbsp;&nbsp;
                <span><?= lang('sampoyigi.local::default.text_24_7_hour'); ?></span>
            <?php }
            else { ?>
                <span class="fa fa-clock-o"></span>&nbsp;&nbsp;
                <span><?= $openingTime; ?> - <?= $closingTime; ?></span>
            <?php } ?>
        </dd>
    <?php } ?>

    <?php if ($openingStatus != 'closed') { ?>
        <dd class="hidden-xs">
            <?php if (!empty($openingType) AND $openingType == '24_7') { ?>
                <span class="fa fa-clock-o"></span>&nbsp;&nbsp;
                <span><?= lang('sampoyigi.local::default.text_24_7_hour'); ?></span>
            <?php }
            else { ?>
                <span class="fa fa-clock-o"></span>&nbsp;&nbsp;
                <span><?= $openingTime; ?> - <?= $closingTime; ?></span>
            <?php } ?>
        </dd>
    <?php } ?>
    <dd class="text-muted">
        <?php if (!$hasDelivery AND $hasCollection) { ?>
            <?= lang('sampoyigi.local::default.text_collection_only'); ?>
        <?php }
        else if ($hasDelivery AND !$hasCollection) { ?>
            <?= lang('sampoyigi.local::default.text_delivery_only'); ?>
        <?php }
        else if ($hasDelivery AND $hasCollection) { ?>
            <?= lang('sampoyigi.local::default.text_both_types'); ?>
        <?php }
        else { ?>
            <?= lang('sampoyigi.local::default.text_no_types'); ?>
        <?php } ?>
    </dd>
    <dd class="text-muted">
        <?= $deliveryConditionText; ?>
    </dd>
</dl>
