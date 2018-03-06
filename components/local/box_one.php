<?php if ($showLocalThumb) { ?>
    <img class="img-responsive pull-left" src="<?= $currentLocation->getThumb(['width' => 80, 'height' => 80]); ?>">
<?php } ?>
<dl <?= $showLocalThumb ? 'class="box-image"' : ''; ?>>
    <dd><h4><?= $currentLocation->getName(); ?></h4></dd>
    <?php if (setting('allow_reviews') != '1') { ?>
        <dd class="text-muted">
            <div class="rating rating-sm">
                <span class="fa fa-star"></span>
                <span class="fa fa-star"></span>
                <span class="fa fa-star"></span>
                <span class="fa fa-star-half-o"></span>
                <span class="fa fa-star-o"></span>
                <span class="small"><?= sprintf(lang('sampoyigi.local::default.text_total_review'), $currentLocation->reviews_count); ?></span>
            </div>
        </dd>
    <?php } ?>
    <dd>
        <span class="text-muted"><?= format_address($currentLocation->getAddress()); ?></span>
    </dd>
</dl>
