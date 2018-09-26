<?php if ($locationCurrent->hasDelivery()) { ?>
    <h4 class="panel-title p-3"><b><?= lang('igniter.local::default.text_delivery_areas'); ?></b></h4>
    <div class="list-group list-group-flush">
        <?php if (count($deliveryAreas)) { ?>
            <div class="list-group-item">
                <div class="row">
                    <div class="col-sm-4"><b><?= lang('igniter.local::default.column_area_name'); ?></b></div>
                    <div class="col-sm-8"><b><?= lang('igniter.local::default.column_area_charge'); ?></b>
                    </div>
                </div>
            </div>
            <?php foreach ($deliveryAreas as $key => $area) { ?>
                <div class="list-group-item">
                    <div class="row">
                        <div class="col-sm-4"><?= $area['name']; ?></div>
                        <div class="col-sm-8">
                            <?php foreach ($area->listConditions() as $id => $condition) {
                                if (empty($condition['amount'])) {
                                    $condition['amount'] = lang('igniter.local::default.text_free');
                                }
                                else if ($condition['amount'] < 0) {
                                    $condition['amount'] = lang('igniter.local::default.text_delivery_not_available');
                                }
                                else {
                                    $condition['amount'] = currency_format($condition['amount']);
                                }

                                $condition['total'] = !empty($condition['total']) ? currency_format($condition['total']) : lang('igniter.local::default.text_delivery_all_orders');
                                ?>
                                <?= ucfirst(strtolower(parse_values($condition, $condition['label']))); ?><br>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php }
        else { ?>
            <div class="list-group-item">
                <p><?= lang('igniter.local::default.text_no_delivery_areas'); ?></p>
            </div>
        <?php } ?>
    </div>
<?php } ?>
