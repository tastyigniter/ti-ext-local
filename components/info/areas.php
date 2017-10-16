<?php if ($hasDelivery) { ?>
    <h4><b><?= lang('sampoyigi.local::default.text_delivery_areas'); ?></b></h4>
    <div class="list-group">
        <?php if (count($deliveryAreas)) { ?>
            <div class="list-group-item">
                <div class="row">
                    <div class="col-xs-4"><b><?= lang('sampoyigi.local::default.column_area_name'); ?></b></div>
                    <div class="col-xs-8 wrap-none"><b><?= lang('sampoyigi.local::default.column_area_charge'); ?></b>
                    </div>
                </div>
            </div>
            <?php foreach ($deliveryAreas as $key => $area) { ?>
                <div class="list-group-item">
                    <div class="row">
                        <div class="col-xs-4"><?= $area['name']; ?></div>
                        <div class="col-xs-8 wrap-none">
                            <?php foreach ($area->listConditions() as $condition) { ?>
                                <?= sprintf($condition['label'],
                                    currency_format($condition['amount']),
                                    currency_format($condition['total'])
                                ); ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php }
        else { ?>
            <div class="list-group-item">
                <p><?= lang('sampoyigi.local::default.text_no_delivery_areas'); ?></p>
            </div>
        <?php } ?>
    </div>

    <h4><b><?= lang('sampoyigi.local::default.text_delivery_map'); ?></b></h4>

    <div id="map">
        <div id="map-holder" style="height:300px;text-align:left;"></div>
    </div>
<?php } ?>
