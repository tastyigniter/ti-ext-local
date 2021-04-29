<div id="local-box">
    <div class="panel local-search">
        <div class="panel-body">
            <div class="row">
                @if (!$hideSearch)
                    <div class="col-sm-12">
                        @partial('@searchbar')
                    </div>
                @endif
            </div>
            @if ($location->requiresUserPosition()
                AND $location->userPosition()->hasCoordinates()
                AND !$location->checkDeliveryCoverage())
                <span class="help-block">@lang('igniter.local::default.text_delivery_coverage')</span>
            @endif
        </div>
    </div>

    @partial($__SELF__.'::default')

    <div class="card mt-1 d-block d-sm-none">
        <div class="card-body">
            <div class="local-timeslot mb-3">
                @partial('@timeslot')
            </div>
            <div class="local-control">
                @partial('@control')
            </div>
        </div>
    </div>
</div>
