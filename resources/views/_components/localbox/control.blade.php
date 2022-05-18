@if (count($locationOrderTypes) <= $__SELF__->property('maxOrderTypeButtons', 2))
    <div
        class="btn-group btn-group-toggle w-100 text-center"
        data-control="order-type-toggle"
        data-handler="{{ $orderTypeEventHandler }}"
    >
        @foreach($locationOrderTypes as $orderType)
            @continue($orderType->isDisabled())
            <input
                id="btn-check-{{$orderType->getCode()}}"
                type="radio"
                name="order_type"
                class="btn-check"
                value="{{ $orderType->getCode() }}"
                {!! $orderType->isActive() ? 'checked="checked"' : '' !!}
            />
            <label
                for="btn-check-{{$orderType->getCode()}}"
                class="btn btn-light w-50 {{ $orderType->isActive() ? 'active' : '' }}"
            >@partial('@control_info', ['orderType' => $orderType])</label>
        @endforeach
    </div>
@else
    <div
        class="dropdown"
        data-control="order-type-toggle"
        data-handler="{{ $orderTypeEventHandler }}"
    >
        <button
            class="btn btn-light btn-block dropdown-toggle"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >
            @partial('@control_info', ['orderType' => $location->getOrderType()])
        </button>
        <div class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton">
            @foreach($locationOrderTypes as $orderType)
                @continue($orderType->isDisabled())
                <a
                    role="button"
                    class="dropdown-item text-center {{ $orderType->isActive() ? 'active' : '' }}"
                    data-order-type-code="{{ $orderType->getCode() }}"
                >
                    @partial('@control_info', ['orderType' => $orderType])
                </a>
            @endforeach
        </div>
    </div>
@endif
@if ($location->orderTypeIsDelivery())
    <p class="text-muted text-center my-2">
        @if ($minOrderTotal = $location->minimumOrder($cart->subtotal()))
            @lang('igniter.local::default.text_min_total'): {{ currency_format($minOrderTotal) }}
        @else
            @lang('igniter.local::default.text_no_min_total')
        @endif
    </p>
@endif
