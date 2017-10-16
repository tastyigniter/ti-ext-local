<dl>
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
    <?php if ($hasDelivery) { ?>
        <dd class="text-muted"><?= $deliveryConditionText; ?></dd>
        <dd class="text-muted">
            <?= ($deliveryCharge > 0)
                ? sprintf(lang('sampoyigi.local::default.text_delivery_charge'), currency_format($deliveryCharge))
                : lang('sampoyigi.local::default.text_free_delivery'); ?>
        </dd>
    <?php } ?>
    <dd class="text-muted">
        <?= $minOrderTotal ? lang('sampoyigi.local::default.text_min_total').':'.currency_format($minOrderTotal) : lang('sampoyigi.local::default.text_no_min_total'); ?>
    </dd>
</dl>
