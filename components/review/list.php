<?php if (count($reviewList)) { ?>
    <ul class="list-group list-group-flush">
        <?php foreach ($reviewList as $review) { ?>
            <li class="list-group-item review-item">
                <?= partial('@item', ['review' => $review]); ?>
            </li>
        <?php } ?>

        <li class="list-group-item">
            <div class="pagination-bar text-right">
                <div class="links"><?= $reviewList->links(); ?></div>
            </div>
        </li>

    </ul>
<?php }
else { ?>
    <p><?= lang('igniter.local::default.text_no_review'); ?></p>
<?php } ?>
