<div class="panel">
    @if (strlen($locationInfo->description))
        <div class="panel-body">
            <h1
                class="h4 wrap-bottom border-bottom"
            >{{ sprintf(lang('igniter.local::default.text_info_heading'), $locationInfo->name) }}</h1>
            <p class="m-0">{!! nl2br($locationInfo->description) !!}</p>
        </div>
    @endif

    <div class="list-group list-group-flush">
        @if ($locationInfo->opensAllDay)
            <div class="list-group-item">@lang('igniter.local::default.text_opens_24_7')</div>
        @endif
        @if ($locationInfo->hasDelivery)
            <div class="list-group-item">
                @lang('igniter.local::default.text_delivery')
                @if ($locationInfo->deliverySchedule->isOpen())
                    {!! sprintf(lang('igniter.local::default.text_in_minutes'), $locationInfo->deliveryMinutes) !!}
                @elseif ($locationInfo->deliverySchedule->isOpening())
                    <span class="text-danger">{!! sprintf(lang('igniter.local::default.text_starts'), make_carbon($locationInfo->deliverySchedule->getOpenTime())->isoFormat($openingTimeFormat)) !!}</span>
                @else
                    @lang('igniter.local::default.text_closed')
                @endif
            </div>
        @endif
        @if ($locationInfo->hasCollection)
            <div class="list-group-item">
                @lang('igniter.local::default.text_collection')
                @if ($locationInfo->collectionSchedule->isOpen())
                    {!! sprintf(lang('igniter.local::default.text_in_minutes'), $locationInfo->collectionMinutes) !!}
                @elseif ($locationInfo->collectionSchedule->isOpening())
                    <span class="text-danger">{!! sprintf(lang('igniter.local::default.text_starts'), make_carbon($locationInfo->collectionSchedule->getOpenTime())->isoFormat($openingTimeFormat)) !!}</span>
                @else
                    @lang('igniter.local::default.text_closed')
                @endif
            </div>
        @endif
        @if ($locationInfo->hasDelivery)
            <div class="list-group-item">
                @lang('igniter.local::default.text_last_order_time')&nbsp;
                <b>{{ $locationInfo->lastOrderTime->isoFormat($lastOrderTimeFormat) }}</b>
            </div>
        @endif
        @if ($locationInfo->payments)
            <div class="list-group-item">
                <i class="fas fa-credit-card fa-fw"></i>&nbsp;<b>@lang('igniter.local::default.text_payments')</b><br/>
                {!! implode(', ', $locationInfo->payments) !!}.
            </div>
        @endif
    </div>

    @partial('@areas')

    <h4 class="panel-title p-3"><b>@lang('igniter.local::default.text_hours')</b></h4>

    @partial('@hours')
</div>

