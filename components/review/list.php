<!--        <h4>--><?php //echo sprintf(lang('sampoyigi.local::default.text_review_heading'), $reviewList['location_name']); ?><!--</h4>-->

<?php if (count($reviewList)) { ?>
    <ul class="list-group">
        <?php foreach ($reviewList as $review) { ?>
            <li class="list-group-item review-item">
                <?= partial('@item', ['review' => $review]); ?>
            </li>
        <?php } ?>

        <li class="list-group-item review-item">
            <div class="pagination-bar text-right">
                <div class="links"><?= $reviewList->links(); ?></div>
            </div>
        </li>

    </ul>
<?php }
else { ?>
    <p><?= lang('sampoyigi.local::default.text_no_review'); ?></p>
<?php } ?>
