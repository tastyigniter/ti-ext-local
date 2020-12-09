@foreach ($locationsList as $locationObject)
    <a
        class="card w-100 p-3 mb-2"
        href="{{ page_url('local/menus', ['location' => $locationObject->permalink]) }}"
    >
        <div class="boxes d-sm-flex no-gutters">
            <div class="col-12 col-sm-7">
                <div class="d-sm-flex">
                    @if ($locationObject->hasThumb)
                        <div class="col-sm-3 p-0 mr-sm-4 mb-3 mb-sm-0">
                            <img
                                class="img-responsive img-fluid"
                                src="{{ $locationObject->thumb }}"
                            />
                        </div>
                    @endif
                    <dl class="no-spacing">
                        <dd><h2 class="h5 mb-0 text-body">{{ $locationObject->name }}</h2></dd>
                        @if ($showReviews)
                            <dd>
                                <div class="rating rating-sm text-muted">
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star-half-o"></span>
                                    <span class="fa fa-star-o"></span>
                                    <span>{!! sprintf(lang('igniter.local::default.review.text_total_review'), $locationObject->reviewsCount) !!}</span>
                                </div>
                            </dd>
                        @endif
                        <dd class="d-none">
                        <span
                            class="text-muted text-truncate">{!! format_address($locationObject->address) !!}</span>
                        </dd>
                        @if ($locationObject->distance)
                            <dd>
                            <span
                                class="text-muted small"
                            ><i class="fa fa-map-marker"></i>&nbsp;&nbsp;{{ number_format($locationObject->distance, 1) }} {{ $distanceUnit }}</span>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>
            <div class="col-12 col-sm-5">
                <dl class="no-spacing">
                    @if ($locationObject->openingSchedule->isOpen())
                        <dt>@lang('igniter.local::default.text_is_opened')</dt>
                    @elseif ($locationObject->openingSchedule->isOpening())
                        <dt class="text-muted">{!! sprintf(lang('igniter.local::default.text_opening_time'), $locationObject->openingTime->isoFormat($openingTimeFormat)) !!}</dt>
                    @else
                        <dt class="text-muted">@lang('igniter.local::default.text_closed')</dt>
                    @endif
                    <dd class="text-muted">
                        @if ($locationObject->hasDelivery)
                            @if ($locationObject->deliverySchedule->isOpen())
                                {!! sprintf(lang('igniter.local::default.text_delivery_time_info'), sprintf(lang('igniter.local::default.text_in_minutes'), $locationObject->deliveryMinutes)) !!}
                            @elseif ($locationObject->deliverySchedule->isOpening())
                                {!! sprintf(lang('igniter.local::default.text_delivery_time_info'), '<span class="text-danger">'.sprintf(lang('igniter.local::default.text_starts'), $locationObject->collectionTime->isoFormat($openingTimeFormat).'</span>')) !!}
                            @else
                                {!! sprintf(lang('igniter.local::default.text_delivery_time_info'), lang('igniter.local::default.text_is_closed')) !!}
                            @endif
                        @endif
                    </dd>
                    <dd class="text-muted">
                        @if ($locationObject->hasCollection)
                            @if ($locationObject->collectionSchedule->isOpen())
                                {!! sprintf(lang('igniter.local::default.text_collection_time_info'), sprintf(lang('igniter.local::default.text_in_minutes'), $locationObject->collectionMinutes)) !!}
                            @elseif ($locationObject->collectionSchedule->isOpening())
                                {!! sprintf(lang('igniter.local::default.text_collection_time_info'), '<span class="text-danger">'.sprintf(lang('igniter.local::default.text_starts'), $locationObject->collectionTime->isoFormat($openingTimeFormat).'</span>')) !!}
                            @else
                                {!! sprintf(lang('igniter.local::default.text_collection_time_info'), lang('igniter.local::default.text_is_closed')) !!}
                            @endif
                        @endif
                    </dd>
                    <dd class="text-muted small">
                        @if (!$locationObject->hasDelivery AND $locationObject->hasCollection)
                            @lang('igniter.local::default.text_only_collection_is_available')
                        @elseif ($locationObject->hasDelivery AND !$locationObject->hasCollection)
                            @lang('igniter.local::default.text_only_delivery_is_available')
                        @elseif ($locationObject->hasDelivery AND $locationObject->hasCollection)
                            @lang('igniter.local::default.text_offers_both_types')
                        @else
                            @lang('igniter.local::default.text_offers_no_types')
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </a>
@endforeach
