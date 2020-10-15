@php
    $menuItemObject = $__SELF__->createMenuItemObject($menuItem);
@endphp
<div id="menu{{ $menuItem->menu_id }}" class="menu-item">
    <div class="d-flex flex-row">
        <?php if ($showMenuImages == 1 AND $menuItem->hasMedia('thumb')) { ?>
        <div
            class="menu-thumb align-self-center mr-3"
            style="width: {{ $menuImageWidth }}px"
        >
            <img
                class="img-responsive img-rounded"
                alt="{{ $menuItem->menu_name }}"
                src="{{ $menuItem->getThumb(['width' => $menuImageWidth, 'height' => $menuImageHeight]) }}"
            >
        </div>
        <?php } ?>

        <div class="menu-content flex-grow-1 mr-3">
            <h6 class="menu-name">{{ $menuItem->menu_name }}</h6>
            <p class="menu-desc text-muted mb-0">
                {!! nl2br($menuItem->menu_description) !!}
            </p>
        </div>
        <div class="menu-detail align-self-start col-3 text-right p-0">
            @if ($menuItemObject->specialIsActive)
                <span class="menu-meta text-muted">
                    <i
                        class="fa fa-star text-warning pr-sm-1"
                        title="{!! sprintf(lang('igniter.local::default.text_end_elapsed'), $menuItemObject->specialDaysRemaining) !!}"
                    ></i>
                </span>
            @endif

            <span class="menu-price pr-sm-3">
                <b>{!! $menuItemObject->menuPrice > 0 ? currency_format($menuItemObject->menuPrice) : lang('main::lang.text_free') !!}</b>
            </span>

            <span class="menu-button">
                <button
                    class="btn btn-light btn-sm btn-cart<?= $menuItemObject->mealtimeIsNotAvailable ? ' disabled' : '' ?>"
                    @if (!$menuItemObject->mealtimeIsNotAvailable)
                    @if ($menuItem->hasOptions())
                    data-cart-control="load-item"
                    data-menu-id="<?= $menuItem->menu_id; ?>"
                    data-quantity="<?= $menuItem->minimum_qty; ?>"
                    @else
                    data-request="<?= $updateCartItemEventHandler; ?>"
                    data-request-data="menuId: '<?= $menuItem->menu_id; ?>', quantity: '<?= $menuItem->minimum_qty; ?>'"
                    data-replace-loading="fa fa-spinner fa-spin"
                    @endif
                    @else
                    title="<?= implode("\r\n", $menuItemObject->mealtimeTitles); ?>"
                    @endif
                >
                    <i class="fa fa-<?= $menuItemObject->mealtimeIsNotAvailable ? 'clock-o' : 'plus' ?>"></i>
                </button>
            </span>
        </div>
    </div>
    <div class="d-flex flex-wrap align-items-center allergens">
        @partial('@allergens', [
        'menuItem' => $menuItem,
        'menuItemObject' => $menuItemObject
        ])
    </div>
</div>
