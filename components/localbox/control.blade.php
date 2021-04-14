@if ($locationCurrent->hasDelivery() OR $locationCurrent->hasCollection())
    @php
        $deliveryTime = make_carbon($location->deliverySchedule()->getOpenTime());
        $collectionTime = make_carbon($location->collectionSchedule()->getOpenTime());
    @endphp
    <div
        class="btn-group btn-group-toggle w-100 text-center"
        data-toggle="buttons"
        data-control="order-type-toggle"
        data-handler="{{ $orderTypeEventHandler }}"
    >
        @if ($locationCurrent->hasDelivery())
            <label
                class="btn btn-light w-50 {{ $location->orderTypeIsDelivery() ? 'active' : '' }}">
                <input
                    type="radio"
                    name="order_type"
                    value="delivery" {{ $location->orderTypeIsDelivery() ? 'checked="checked"' : '' }}
                />&nbsp;&nbsp;
                <strong>@lang('igniter.local::default.text_delivery')</strong>
                <span class="small center-block">
                    @if ($location->deliverySchedule()->isOpen())
                        {!! sprintf(lang('igniter.local::default.text_in_min'), $locationCurrent->deliveryMinutes()) !!}
                    @elseif ($location->deliverySchedule()->isOpening())
                        {!! sprintf(lang('igniter.local::default.text_starts'), $deliveryTime->isoFormat($openingTimeFormat)) !!}
                    @else
                        @lang('igniter.cart::default.text_is_closed')
                    @endif
                </span>
            </label>
        @endif
        @if ($locationCurrent->hasCollection())
            <label class="btn btn-light w-50 {{ $location->orderTypeIsCollection() ? 'active' : '' }}">
                <input
                    type="radio"
                    name="order_type"
                    value="collection"
                    {!! $location->orderTypeIsCollection() ? 'checked="checked"' : '' !!}
                />&nbsp;&nbsp;
                <strong>@lang('igniter.local::default.text_collection')</strong>
                <span
                    class="small center-block">
                        @if ($location->collectionSchedule()->isOpen())
                        {!! sprintf(lang('igniter.local::default.text_in_min'), $locationCurrent->collectionMinutes()) !!}
                    @elseif ($location->collectionSchedule()->isOpening())
                        {!! sprintf(lang('igniter.local::default.text_starts'), $collectionTime->isoFormat($openingTimeFormat)) !!}
                    @else
                        @lang('igniter.cart::default.text_is_closed')
                    @endif
                </span>
            </label>
        @endif
    </div>
    @if ($location->orderTypeIsDelivery())
        <p class="text-muted text-center my-2">
            @if ($minOrderTotal = $location->minimumOrder($cart->subtotal()))
                @lang('igniter.local::default.text_min_total'): {{ currency_format($minOrderTotal) }}
            @else
                @lang('igniter.local::default.text_no_min_total')
            @endif
        </p>
    @endif
@endif
