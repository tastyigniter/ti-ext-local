<?php foreach ($locationsList as $location) { ?>
    <?php
    $openingSchedule = $location->newWorkingSchedule('opening');
    $deliverySchedule = $location->newWorkingSchedule('delivery');
    $collectionSchedule = $location->newWorkingSchedule('collection');
    $hasDelivery = $location->hasDelivery();
    $hasCollection = $location->hasCollection();
    $distance = ($coordinates = $userPosition->getCoordinates())
        ? $location->calculateDistance($coordinates) : null;
    $deliveryMinutes = $location->deliveryMinutes();
    $collectionMinutes = $location->collectionMinutes();
    ?>
    <a
        class="card w-100 p-3 mb-1"
        href="<?= page_url('local/menus', ['location' => $location->permalink_slug]); ?>"
    >
        <div class="row boxes">
            <?php if (!empty($location->location_image)) { ?>
                <div class="col-12 col-sm-3">
                    <img class="img-responsive pull-left"
                         src="<?= $location->getThumb(['height' => 90, 'width' => 90]); ?>">
                </div>
            <?php } ?>
            <div class="col-12 col-sm-5">
                <dl>
                    <dd><h2 class="h6 mb-0 text-body"><?= $location->location_name; ?></h2></dd>
                    <?php if ($showReviews) { ?>
                        <dd>
                            <div class="rating rating-sm text-muted">
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star"></span>
                                <span class="fa fa-star-half-o"></span>
                                <span class="fa fa-star-o"></span>
                                <span><?= sprintf(lang('igniter.local::default.text_total_review'), $location->reviews_count); ?></span>
                            </div>
                        </dd>
                    <?php } ?>
                    <dd>
                        <span class="text-muted text-truncate"><?= format_address($location->getAddress()); ?></span>
                    </dd>
                    <?php if ($distance) { ?>
                        <dd>
                            <span class="text-muted"><?= number_format($distance, 1); ?> <?= $distanceUnit; ?></span>
                        </dd>
                    <?php } ?>
                </dl>
            </div>
            <div class="col-12 col-sm-4">
                <dl>
                    <?php if ($openingSchedule->isOpen()) { ?>
                        <dt><?= lang('igniter.local::default.text_is_opened'); ?></dt>
                    <?php }
                    else if ($openingSchedule->isOpening()) { ?>
                        <dt class="text-muted"><?= sprintf(lang('igniter.local::default.text_opening_time'), $openingSchedule->getOpenTime($timeFormat)); ?></dt>
                    <?php }
                    else { ?>
                        <dt class="text-muted"><?= lang('igniter.local::default.text_closed'); ?></dt>
                    <?php } ?>
                    <dd class="text-muted">
                        <?php if ($hasDelivery) { ?>
                            <?php if ($deliverySchedule->isOpen()) { ?>
                                <?= sprintf(lang('igniter.local::default.text_delivery_time_info'), sprintf(lang('igniter.local::default.text_in_minutes'), $deliveryMinutes)); ?>
                            <?php }
                            else if ($deliverySchedule->isOpening()) { ?>
                                <?= sprintf(lang('igniter.local::default.text_delivery_time_info'), sprintf(lang('igniter.local::default.text_starts'), $deliverySchedule->getOpenTime($timeFormat))); ?>
                            <?php }
                            else { ?>
                                <?= sprintf(lang('igniter.local::default.text_delivery_time_info'), lang('igniter.local::default.text_is_closed')); ?>
                            <?php } ?>
                        <?php } ?>
                    </dd>
                    <dd class="text-muted">
                        <?php if ($hasCollection) { ?>
                            <?php if ($collectionSchedule->isOpen()) { ?>
                                <?= sprintf(lang('igniter.local::default.text_collection_time_info'), sprintf(lang('igniter.local::default.text_in_minutes'), $collectionMinutes)); ?>
                            <?php }
                            else if ($collectionSchedule->isOpening()) { ?>
                                <?= sprintf(lang('igniter.local::default.text_collection_time_info'), sprintf(lang('igniter.local::default.text_starts'), $collectionSchedule->getOpenTime($timeFormat))); ?>
                            <?php }
                            else { ?>
                                <?= sprintf(lang('igniter.local::default.text_collection_time_info'), lang('igniter.local::default.text_is_closed')); ?>
                            <?php } ?>
                        <?php } ?>
                    </dd>
                    <dd class="text-muted small">
                        <?php if (!$hasDelivery AND $hasCollection) { ?>
                            <?= lang('igniter.local::default.text_only_collection_is_available'); ?>
                        <?php }
                        else if ($hasDelivery AND !$hasCollection) { ?>
                            <?= lang('igniter.local::default.text_only_delivery_is_available'); ?>
                        <?php }
                        else if ($hasDelivery AND $hasCollection) { ?>
                            <?= lang('igniter.local::default.text_offers_both_types'); ?>
                        <?php }
                        else { ?>
                            <?= lang('igniter.local::default.text_offers_no_types'); ?>
                        <?php } ?>
                    </dd>
                </dl>
            </div>
        </div>
    </a>
<?php } ?>
