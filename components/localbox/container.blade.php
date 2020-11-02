<div id="local-box">
    <div class="panel local-search">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-{{ $hideSearch ? '12' : '4' }} mb-3 mb-sm-0">
                    <div id="local-timeslot">
                        @partial('@timeslot')
                    </div>
                </div>
                @if (!$hideSearch)
                    <div class="col-sm-8">
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
</div>