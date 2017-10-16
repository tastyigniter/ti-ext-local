<?php if (strlen($localModel->description)) { ?>
    <div class="panel panel-default">
        <div class="panel-body">
            <h4
                class="wrap-bottom border-bottom"
            ><?= sprintf(lang('sampoyigi.local::default.text_info_heading'), $localModel->location_name); ?></h4>
            <p><?= $localModel->description; ?></p>
        </div>
    </div>
<?php } ?>


<div class="row wrap-bottom">
    <div class="col-sm-6">
        <div class="list-group">
            <?php if (!empty($workingHourType['opening']) AND $workingHourType['opening'] == '24_7') { ?>
                <div class="list-group-item"><?= lang('sampoyigi.local::default.text_opens_24_7'); ?></div>
            <?php } ?>
            <?php if ($hasDelivery) { ?>
                <div class="list-group-item">
                    <?= lang('sampoyigi.local::default.text_delivery_time'); ?>
                    <?php if ($deliveryStatus == 'open') { ?>
                        <?= sprintf(lang('sampoyigi.local::default.text_in_minutes'), $deliveryTime); ?>
                    <?php } else if ($deliveryStatus == 'opening') { ?>
                        <?= sprintf(lang('sampoyigi.local::default.text_starts'), $deliveryHour); ?>
                    <?php } else { ?>
                        <?= lang('sampoyigi.local::default.text_closed'); ?>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if ($hasCollection) { ?>
                <div class="list-group-item">
                    <?= lang('sampoyigi.local::default.text_collection_time'); ?>
                    <?php if ($collectionStatus == 'open') { ?>
                        <?= sprintf(lang('sampoyigi.local::default.text_in_minutes'), $collectionTime); ?>
                    <?php } else if ($collectionStatus == 'opening') { ?>
                        <?= sprintf(lang('sampoyigi.local::default.text_starts'), $collectionHour); ?>
                    <?php } else { ?>
                        <?= lang('sampoyigi.local::default.text_closed'); ?>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if ($hasDelivery) { ?>
                <div class="list-group-item">
                    <?= lang('sampoyigi.local::default.text_last_order_time'); ?>&nbsp;
                    <?= $lastOrderTime; ?>
                </div>
            <?php } ?>
            <?php if (count($localPayments)) { ?>
                <div class="list-group-item">
                    <i class="fa fa-paypal fa-fw"></i>&nbsp;<b><?= lang('sampoyigi.local::default.text_payments'); ?></b><br/>
                    <?= implode(', ', array_column($localPayments, 'name')); ?>.
                </div>
            <?php } ?>
        </div>

        <h4><b><?= lang('sampoyigi.local::default.text_hours'); ?></b></h4>
        <?= partial('@hours') ?>
    </div>
    <div class="col-sm-6">
        <?= partial('@areas') ?>
    </div>
</div>

<script type="text/javascript">//<![CDATA[
    //    var map = null;
    //    var geocoder = null;
    //    var bounds = null;
    //    var markers = [];
    //    var deliveryAreas = [];
    //    var infoWindow = null;
    //    var colors = <?//= json_encode($area_colors); ?>//;
    //    var delivery_areas = <?//= json_encode($delivery_areas); ?>//;
    //    var local_name = "<?//= $location_name; ?>//";
    //    var latlng = new google.maps.LatLng(
    //        parseFloat("<?//= $location_lat; ?>//"),
    //        parseFloat("<?//= $location_lng; ?>//")
    //    );
    //
    //    jQuery('document').ready(function () {
    //        if (map == null) {
    //            initializeMap();
    //        }
    //    });
    //
    //    function initializeMap() {
    //        var html = "<b>" + local_name + "</b> <br/>" +
    //            "<?//= $map_address; ?>//<br/>" +
    //            "<?//= $location_telephone; ?>//";
    //
    //        var mapOptions = {
    //            scrollwheel: false,
    //            center: latlng,
    //            zoom: 16,
    //            mapTypeId: google.maps.MapTypeId.ROADMAP
    //        };
    //
    //        map = new google.maps.Map(document.getElementById('map-holder'), mapOptions);
    //
    //        var infowindow = new google.maps.InfoWindow({
    //            content: html
    //        });
    //
    //        var marker = new google.maps.Marker({
    //            position: latlng,
    //            map: map,
    //            title: local_name
    //        });
    //
    //        google.maps.event.addListener(marker, 'click', function () {
    //            infowindow.open(map, marker);
    //        });
    //
    //        createSavedArea(delivery_areas)
    //    }
    //
    //    function defaultAreaOptions() {
    //        return {
    //            visible: false,
    //            draggable: false,
    //            strokeOpacity: 0.8,
    //            strokeWeight: 3,
    //            fillOpacity: 0.15
    //        };
    //    }
    //
    //    function resizeMap() {
    //        var allAreasBounds;
    //
    //        if (!deliveryAreas.length) {
    //            return;
    //        }
    //
    //        allAreasBounds = deliveryAreas[0].getBounds();
    //        deliveryAreas.forEach(function (area) {
    //            var bounds = area.getBounds();
    //            allAreasBounds.union(bounds);
    //        });
    //
    //        map.fitBounds(allAreasBounds);
    //    }
    //
    //    function drawShapeArea(row, shape) {
    //        var options, shapeArea,
    //            color = (colors[row] == undefined) ? '#F16745' : colors[row];
    //
    //        options = defaultAreaOptions();
    //        options.paths = shape;
    //        options.strokeColor = color;
    //        options.fillColor = color;
    //        shapeArea = new google.maps.Polygon(options);
    //        shapeArea.setMap(map);
    //        deliveryAreas.push(shapeArea);
    //
    //        shapeArea.row = row;
    //        shapeArea.type = 'shape';
    //
    //        return shapeArea;
    //    }
    //
    //    function drawCircleArea(row, center, radius) {
    //        var options, circleArea,
    //            color = (colors[row] == undefined) ? '#F16745' : colors[row];
    //
    //        options = defaultAreaOptions();
    //        options.strokeColor = color;
    //        options.fillColor = color;
    //        options.center = center;
    //        options.radius = radius;
    //        circleArea = new google.maps.Circle(options);
    //        circleArea.setMap(map);
    //        deliveryAreas.push(circleArea);
    //
    //        circleArea.row = row;
    //        circleArea.type = 'circle';
    //
    //        return circleArea;
    //    }
    //
    //    function unserializedAreas(delivery_areas) {
    //        var savedAreas = [];
    //
    //        for (i = 0; i < delivery_areas.length; i++) {
    //            var shape = delivery_areas[i].shape;
    //            var circle = delivery_areas[i].circle;
    //            var type = delivery_areas[i].type;
    //
    //            try {
    //                shape = JSON.parse(shape);
    //                circle = JSON.parse(circle);
    //            } catch (e) {
    //                console.log(e);
    //            }
    //
    //            savedAreas.push({
    //                shape: shape[0].shape,
    //                center: circle[0].center,
    //                radius: circle[1].radius,
    //                type: type,
    //                row: i
    //            });
    //        }
    //
    //        return savedAreas;
    //    }
    //
    //    function createSavedArea(delivery_areas) {
    //        var savedAreas = unserializedAreas(delivery_areas);
    //
    //        savedAreas.forEach(function (area) {
    //            var shapeArea, circleArea,
    //                shape, decodedPath;
    //
    //            if (area.center != undefined && area.radius != undefined) {
    //                center = new google.maps.LatLng(area.center.lat, area.center.lng);
    //                circleArea = drawCircleArea(area.row, center, area.radius);
    //            }
    //
    //            if (area.shape != undefined) {
    //                shape = area.shape.replace(/,/g, '\\').replace(/-/g, '\/');
    //                decodedPath = google.maps.geometry.encoding.decodePath(shape);
    //
    //                shapeArea = drawShapeArea(area.row, decodedPath);
    //            }
    //
    //            if (area.type == 'circle') {
    //                toggleVisibleMapArea(circleArea, 'circle');
    //            } else {
    //                toggleVisibleMapArea(shapeArea, 'shape');
    //            }
    //        });
    //
    //        resizeMap();
    //    }
    //
    //    function toggleVisibleMapArea(deliveryArea, type) {
    //        deliveryAreas.forEach(function (area) {
    //            if (area.row == deliveryArea.row) {
    //                area.setOptions({visible: false});
    //                if (type != undefined && area.type == type) {
    //                    area.setOptions({visible: true});
    //                }
    //            }
    //        });
    //    }
    //
    //    if (!google.maps.Polygon.prototype.getBounds) {
    //        google.maps.Polygon.prototype.getBounds = function () {
    //            var bounds = new google.maps.LatLngBounds();
    //            var paths = this.getPaths();
    //            var path;
    //            for (var i = 0; i < paths.getLength(); i++) {
    //                path = paths.getAt(i);
    //                for (var ii = 0; ii < path.getLength(); ii++) {
    //                    bounds.extend(path.getAt(ii));
    //                }
    //            }
    //            return bounds;
    //        }
    //    }
    //]]></script>