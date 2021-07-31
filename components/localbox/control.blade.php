<div
    class="btn-group btn-group-toggle w-100 text-center"
    data-toggle="buttons"
    data-control="order-type-toggle"
    data-handler="{{ $orderTypeEventHandler }}"
>
    @foreach($locationOrderTypes as $orderType)
        @continue($orderType->isDisabled())
        @php
            $openingTime = make_carbon($orderType->getSchedule()->getOpenTime());
        @endphp
        <label class="btn btn-light w-50 {{ $orderType->isActive() ? 'active' : '' }}">
            <input
                type="radio"
                name="order_type"
                value="{{ $orderType->getCode() }}"
                {!! $orderType->isActive() ? 'checked="checked"' : '' !!}
            />&nbsp;&nbsp;
            <strong>{{ $orderType->getLabel() }}</strong>
            <span
                class="small center-block">
                @if ($orderType->getSchedule()->isOpen())
                    {!! sprintf(lang('igniter.local::default.text_in_min'), $orderType->getLeadTime()) !!}
                @elseif ($orderType->getSchedule()->isOpening())
                    {!! sprintf(lang('igniter.local::default.text_starts'), $openingTime->isoFormat($openingTimeFormat)) !!}
                @else
                    @lang('igniter.cart::default.text_is_closed')
                @endif
            </span>
        </label>
    @endforeach
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
