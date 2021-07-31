<div class="card mb-3">
    <div class="card-body">
        <div class="container"><div class="row">
        <div class="col-sm">
        @lang('igniter.local::default.label_customer_location')
        <form
            method="GET"
            id="user-location-form"
            class="form-search form-horizontal"
            action="{{ current_url() }}"
        >
            <div class="input-group">
                <div class="input-group-prepend">
                    <span
                        class="input-group-text"
                    ><i class="fa fa-map-marker"></i></span>
                </div>
                <?php
                    foreach($_GET as $name => $value) {
                        $name = htmlspecialchars($name);
                        $value = htmlspecialchars($value);
                        if ($name != "locationSearch" and $name != "lat" and $name != "lng") {
                            echo '<input type="hidden" name="'. $name .'" value="'. $value .'">';
                        }
                    }
                ?>
                <input
                    type="search"
                    class="form-control"
                    name="locationSearch"
                    id="locationSearchField"
                    value="{{ trim($userPosition->format()) }}"
                    placeholder="@lang('igniter.local::default.label_search_query')"
                />
                <span class="input-group-append btn-group">
                    <button
                        class="btn btn-light"
                        type="submit"
                    ><i class="fa fa-search"></i></button>
                    <a
                        class="btn btn-light"
                        onclick="getLocation();"
                        title="@lang('igniter.local::default.label_find_location')"
                    ><i class="fa fa-location-arrow"></i></a>
                </span>
            </div>
        </form>
        </div>
        <div class="col-sm">
        @lang('igniter.local::default.label_search_locations')
        <form
            method="GET"
            id="filter-search-form"
            class="form-search form-horizontal"
            action="{{ current_url() }}"
        >
            <div class="input-group">
                <?php
                    foreach($_GET as $name => $value) {
                        $name = htmlspecialchars($name);
                        $value = htmlspecialchars($value);
                        if ($name != "search") {
                            echo '<input type="hidden" name="'. $name .'" value="'. $value .'">';
                        }
                    }
                ?>
                <input
                    type="search"
                    class="form-control"
                    name="search"
                    value="{{ $filterSearch }}"
                    placeholder="@lang('igniter.local::default.text_filter_search')"
                />
                <span class="input-group-append">
                    <button
                        class="btn btn-light"
                        type="submit"
                    ><i class="fa fa-search"></i></button>
                </span>
            </div>
        </form>
        </div>
        </div></div>
    </div>
</div>

<script>
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(setPosition, positionError, {timeout:10000});
        } else {
            //Geolocation is not supported by this browser
            window.alert("Geolocation is not supported by your browser.");
        }
    }

    function positionError(error) {
        alert("Error getting your location.");
    }

    function setPosition(position) {
        window.location.replace(window.location.href + "&lat=" + position.coords.latitude + "&lng=" + position.coords.longitude);
    }
</script>
