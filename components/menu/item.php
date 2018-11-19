<?php
$mealtime = $menuItem->mealtime;
$special = $menuItem->special;
$mealtimeNotAvailable = ($mealtime AND !$mealtime->isAvailableNow());
$specialActive = ($special AND $special->active());
$menuHasOptions = $menuItem->hasOptions();
$menuPrice = $specialActive ? $special->special_price : $menuItem->menu_price;
?>
<div id="menu<?= $menuItem->menu_id; ?>" class="menu-item mb-3">
    <div class="d-flex flex-row">
        <?php if ($showMenuImages == 1 AND $menuItem->hasMedia('thumb')) { ?>
            <div
                class="menu-thumb align-self-center mr-3"
                style="width: <?= $menuImageWidth ?>px"
            >
                <img
                    class="img-responsive img-rounded"
                    alt="<?= $menuItem->menu_name; ?>"
                    src="<?= $menuItem->getThumb([
                        'width' => $menuImageWidth,
                        'height' => $menuImageHeight,
                    ]); ?>"
                >
            </div>
        <?php } ?>

        <div class="menu-content w-75 flex-grow-1 mr-3">
            <span class="menu-name">
                <b><?= str_limit($menuItem->menu_name, 80); ?></b>
            </span>
            <p class="menu-desc text-muted mb-0">
                <?= str_limit($menuItem->menu_description, 120); ?>
            </p>
        </div>
        <div class="menu-detail align-self-center col-3 text-right p-0">
            <span class="menu-meta text-muted">
                <?php if ($specialActive) { ?>
                    <?php
                    $specialDaysRemaining = $special->daysRemaining();
                    $specialDaysText = sprintf(lang('igniter.local::default.text_end_elapsed'),
                        $special->end_date->diffForHumans());
                    ?>
                    <i class="fa fa-star text-warning pr-sm-1" title="<?= $specialDaysText; ?>"></i>
                <?php } ?>
            </span>

            <span class="menu-price pr-sm-2">
                <b><?= $menuPrice > 0 ? currency_format($menuPrice) : lang('igniter.local::default.text_free'); ?></b>
            </span>

            <span class="menu-button">
                <i class="fa fa-spinner fa-spin mr-2" style="display: none"></i>
                <button
                    class="btn btn-light btn-sm btn-cart<?= $mealtimeNotAvailable ? ' disabled' : '' ?>"
                    <?php if (!$mealtimeNotAvailable) { ?>
                        data-cart-control="<?= $menuHasOptions ? 'load-item' : 'add-item'; ?>"
                        data-menu-id="<?= $menuItem->menu_id; ?>"
                        data-quantity="<?= $menuItem->minimum_qty; ?>"
                    <?php } else { ?>
                        title="<?= sprintf(lang('igniter.local::default.text_mealtime'),
                            $mealtime->mealtime_name, $mealtime->start_time, $mealtime->end_time
                        ); ?>"
                    <?php } ?>
                >
                    <i class="fa fa-<?= $mealtimeNotAvailable ? 'clock-o' : 'plus' ?>"></i>
                </button>
            </span>
        </div>
    </div>
</div>
