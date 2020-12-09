<div id="local-box">
    <div class="panel local-search">
        <div class="panel-body">
            <div class="row">
                @if (!$hideSearch)
                    <div class="col-sm-12">
                        @partial('@searchbar')
                    </div>
                @endif
                <div class="col-sm-12{{ $hideSearch ? '' : ' mt-3 mt-sm-0' }} d-block d-sm-none">
                    <div class="local-timeslot">
                        @partial('@timeslot')
                    </div>
                </div>
                <div class="col-sm-12 mt-3 mt-sm-0 d-block d-sm-none">
                    <div class="local-control">
                        @partial('@control')
                    </div>
                </div>
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
