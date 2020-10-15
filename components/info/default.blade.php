<div class="panel">
    @if (strlen($description = $infoLocationCurrent->getDescription()))
        <div class="panel-body">
            <h1
                class="h4 wrap-bottom border-bottom"
            >{{ sprintf(lang('igniter.local::default.text_info_heading'), $infoLocationCurrent->getName()) }}</h1>
            <p class="m-0">{!! nl2br($description) !!}</p>
        </div>
    @endif

    <div class="list-group list-group-flush">
        @if ($infoLocationCurrent->workingHourType('opening') == '24_7')
            <div class="list-group-item">@lang('igniter.local::default.text_opens_24_7')</div>
        @endif
        @if ($infoLocationCurrent->hasDelivery())
            <div class="list-group-item">
                @lang('igniter.local::default.text_delivery')
                @if ($infoLocation->deliverySchedule()->isOpen())
                    {!! sprintf(lang('igniter.local::default.text_in_minutes'), $infoLocationCurrent->deliveryMinutes()) !!}
                @elseif ($infoLocation->deliverySchedule()->isOpening())
                    <span class="text-danger">{!! sprintf(lang('igniter.local::default.text_starts'), make_carbon($infoLocation->deliverySchedule()->getOpenTime())->isoFormat($openingTimeFormat)) !!}</span>
                @else
                    @lang('igniter.local::default.text_closed')
                @endif
            </div>
        @endif
        @if ($infoLocationCurrent->hasCollection())
            <div class="list-group-item">
                @lang('igniter.local::default.text_collection')
                @if ($infoLocation->collectionSchedule()->isOpen())
                    {!! sprintf(lang('igniter.local::default.text_in_minutes'), $infoLocationCurrent->collectionMinutes()) !!}
                @elseif ($infoLocation->collectionSchedule()->isOpening())
                    <span class="text-danger">{!! sprintf(lang('igniter.local::default.text_starts'), make_carbon($infoLocation->collectionSchedule()->getOpenTime())->isoFormat($openingTimeFormat)) !!}</span>
                @else
                    @lang('igniter.local::default.text_closed')
                @endif
            </div>
        @endif
        @if ($infoLocationCurrent->hasDelivery())
            <div class="list-group-item">
                @lang('igniter.local::default.text_last_order_time')&nbsp;
                <b>{{ $infoLocation->lastOrderTime()->isoFormat($lastOrderTimeFormat) }}</b>
            </div>
        @endif
        @if ($localPayments->isNotEmpty())
            <div class="list-group-item">
                <i class="fas fa-credit-card fa-fw"></i>&nbsp;<b>@lang('igniter.local::default.text_payments')</b><br/>
                {!! implode(', ', $localPayments->pluck('name')->all()) !!}.
            </div>
        @endif
    </div>

    @partial('@areas')

    <h4 class="panel-title p-3"><b>@lang('igniter.local::default.text_hours')</b></h4>

    @partial('@hours')
</div>

