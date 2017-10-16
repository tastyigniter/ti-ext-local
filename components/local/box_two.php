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

    <dd class="text-muted">
        <?php if ($hasDelivery) { ?>
            <?php $deliveryTimeLang = ($deliveryStatus == 'open') ? lang('sampoyigi.local::default.text_in_minutes') : lang('sampoyigi.local::default.text_starts'); ?>
            <?= sprintf(lang('sampoyigi.local::default.text_delivery_time_info'), ($deliveryStatus != 'closed')
                ? sprintf($deliveryTimeLang, $deliveryTime) : lang('sampoyigi.local::default.text_is_closed')); ?>
        <?php } ?>
    </dd>
    <dd class="text-muted">
        <?php if ($hasCollection) { ?>
            <?php $collectionTimeLang = ($collectionStatus == 'open') ? lang('sampoyigi.local::default.text_in_minutes') : lang('sampoyigi.local::default.text_starts'); ?>
            <?= sprintf(lang('sampoyigi.local::default.text_collection_time_info'), ($collectionStatus != 'closed')
                ? sprintf($collectionTimeLang, $collectionTime) : lang('sampoyigi.local::default.text_is_closed')); ?>
        <?php } ?>
    </dd>
</dl>
