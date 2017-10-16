<?php if ($showLocalThumb) { ?>
    <img class="img-responsive pull-left" src="<?= $localThumb; ?>">
<?php } ?>
<dl <?= $showLocalThumb ? 'class="box-image"' : ''; ?>>
    <dd><h4><?= $localName; ?></h4></dd>
    <?php if (config_item('allow_reviews') != '1') { ?>
        <dd class="text-muted">
            <div class="rating rating-sm">
                <span class="fa fa-star"></span>
                <span class="fa fa-star"></span>
                <span class="fa fa-star"></span>
                <span class="fa fa-star-half-o"></span>
                <span class="fa fa-star-o"></span>
                <span class="small"><?= sprintf(lang('sampoyigi.local::default.text_total_review'), $countReviews); ?></span>
            </div>
        </dd>
    <?php } ?>
    <dd>
        <span class="text-muted"><?= str_replace('<br />', ', ', $localAddress); ?></span>
    </dd>
</dl>
