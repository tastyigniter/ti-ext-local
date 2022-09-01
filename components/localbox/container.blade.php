<div id="local-box">
    @if ($location->orderTypeIsDelivery())
        @if (($alias = $__SELF__->property('localSearchAlias')) && has_component($alias))
            <div class="panel local-search">
                <div class="panel-body">
                    <div id="local-search-container">
                        @partial($alias.'::container')
                    </div>
                </div>
            </div>
        @endif
    @endif

    @partial($__SELF__.'::default')

    <div class="card mt-1 d-block d-lg-none">
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
