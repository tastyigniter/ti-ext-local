<?php if (count($localHours)) { ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th></th>
                <th><?= lang('sampoyigi.local::default.text_opening'); ?></th>
                <?php if ($hasDelivery) { ?>
                    <th><?= lang('sampoyigi.local::default.text_delivery'); ?></th>
                <?php } ?>
                <?php if ($hasCollection) { ?>
                    <th><?= lang('sampoyigi.local::default.text_collection'); ?></th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($localHours['opening'] as $day => $openingHour) { ?>
                <tr>
                    <td><?= $openingHour['day']; ?></td>
                    <?php foreach ($workingTypes as $type) { ?>
                        <?php if (isset($localHours[$type][$day])) { ?>
                            <?php
                            $hour = $localHours[$type][$day];
                            ?>
                            <td<?= count($localHours) == 1 ? ' colspan="9" class="text-center"' : ''; ?>>
                                <span class="small">
                                    <?php
                                    if (!empty($hour['status']))
                                        echo sprintf(lang('sampoyigi.local::default.text_working_hour'),
                                            mdate($localTimeFormat, $hour['open']->timestamp),
                                            mdate($localTimeFormat, $hour['close']->timestamp)
                                        );
                                    ?>
                                </span>
                                <span class="small text-muted">
                                    <?php if ($hour['status'] != '1') {
                                        echo lang('sampoyigi.local::default.text_closed');
                                    }
                                    else if (isset($hour['open_all_day']) AND $hour['open_all_day'] == TRUE) {
                                        echo lang('sampoyigi.local::default.text_24h');
                                    }; ?>
                                </span>
                            </td>
                        <?php }
                        else if (count($localHours) > 1) { ?>
                            <td colspan="9"><?= lang('sampoyigi.local::default.text_same_as_opening_hours'); ?></td>
                        <?php } ?>
                    <?php } ?>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<?php } ?>
