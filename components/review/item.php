<blockquote>
    <p class="review-text"><?= $review['text']; ?></p>
    <div class="rating-row row">
        <div class="col-xs-4 col-sm-3">
            <b>Quality:</b>
            <div class="rating rating-star"
                 data-score="<?= $review['quality']; ?>"
                 data-readonly="true"></div>
        </div>
        <div class="col-xs-4 col-sm-3">
            <b>Delivery:</b>
            <div class="rating rating-star"
                 data-score="<?= $review['delivery']; ?>"
                 data-readonly="true"></div>
        </div>
        <div class="col-xs-4 col-sm-3">
            <b>Service:</b>
            <div class="rating rating-star"
                 data-score="<?= $review['service']; ?>"
                 data-readonly="true"></div>
        </div>
    </div>
    <small>
        <?= $review['author']; ?><?= lang('sampoyigi.local::default.text_from'); ?>
        <cite title="<?= lang('sampoyigi.local::default.text_source'); ?>"><?= $review['city']; ?></cite><?= lang('sampoyigi.local::default.text_on'); ?>
        <?= $review['date']; ?>
    </small>
</blockquote>
