<?php
$mealtime = $menuItem->mealtime;
$special = $menuItem->special;
$mealtimeNotAvailable = ($mealtime AND !$mealtime->availableNow());
$specialActive = ($special AND $special->active());
$menuHasOptions = $menuItem->hasOptions();
$menuPrice = $specialActive ? $special->special_price : $menuItem->menu_price;
?>
<div id="menu<?= $menuItem->menu_id; ?>" class="menu-item">
    <div class="menu-item-wrapper">
        <?php if ($showMenuImages == '1' AND !empty($menuItem->menu_photo)) { ?>
            <div class="menu-thumb center-vertical">
                <img
                    class="img-responsive img-thumbnail"
                    alt="<?= $menuItem->menu_name; ?>"
                    src="<?= $menuItem->getThumb([
                        'width' => $menuThumWidth,
                        'height' => $menuThumHeight,
                    ]); ?>"
                >
            </div>
        <?php } ?>

        <div class="menu-content center-vertical">
            <span class="menu-name">
                <b><?= str_limit($menuItem->menu_name, 80); ?></b>
            </span>
            <span class="menu-desc small">
                <?= str_limit($menuItem->menu_description, 120); ?>
            </span>
        </div>
        <div class="menu-detail center-vertical">
            <span class="menu-meta text-muted">
                <?php if ($mealtimeNotAvailable) { ?>
                    <i
                        class="fa fa-clock-o"
                        title="<?= sprintf(lang('sampoyigi.local::default.text_mealtime'), $mealtime->mealtime_name, $mealtime->start_time, $mealtime->end_time); ?>"
                    ></i>
                <?php } ?>

                <?php if ($specialActive) { ?>
                    <?php
                    $specialDaysRemaining = $special->daysRemaining();
                    $specialDaysText = sprintf(lang('sampoyigi.local::default.text_end_elapsed'),
                        timespan(time(), $special->end_date->getTimeStamp(), 4));
                    ?>
                    <i class="menu-special fa fa-star" title="<?= $specialDaysText; ?>"></i>
                <?php } ?>
            </span>

            <span class="menu-price">
                <b><?= $menuPrice > 0 ? currency_format($menuPrice) : lang('sampoyigi.local::default.text_free'); ?></b>
            </span>

            <span class="menu-button">
                <?php if ($mealtimeNotAvailable) { ?>
                    <a class="btn btn-default btn-cart disabled">
                        <span class="fa fa-plus text-primary"></span>
                    </a>
                <?php } else { ?>
                    <a
                        class="btn btn-default btn-cart"
                        data-cart-control="<?= $menuHasOptions ? 'load-item' : 'add-item'; ?>"
                        data-menu-id="<?= $menuItem->menu_id; ?>"
                        data-quantity="<?= $menuItem->minimum_qty; ?>"
                    >
                        <span class="fa fa-plus text-primary"></span>
                    </a>
                <?php } ?>
            </span>
        </div>
    </div>
</div>
