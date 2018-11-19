<?php if ($showLocalThumb) { ?>
    <img class="img-responsive pull-left"
         src="<?= $locationCurrent->getThumb(['width' => $localThumbWidth, 'height' => $localThumbHeight]); ?>">
<?php } ?>
<dl <?= $showLocalThumb ? 'class="box-image"' : ''; ?>>
    <dd><h1 class="h4"><?= $locationCurrent->getName(); ?></h1></dd>
    <?php if (setting('allow_reviews', 1)) { ?>
        <dd class="text-muted">
            <div class="rating rating-sm">
                <span class="fa fa-star"></span>
                <span class="fa fa-star"></span>
                <span class="fa fa-star"></span>
                <span class="fa fa-star-half-o"></span>
                <span class="fa fa-star-o"></span>
                <span
                    class="small"><?= sprintf(lang('igniter.local::default.text_total_review'), $locationCurrent->reviews_count); ?></span>
            </div>
        </dd>
    <?php } ?>
    <dd>
        <span class="text-muted"><?= format_address($locationCurrent->getAddress()); ?></span>
    </dd>
</dl>
