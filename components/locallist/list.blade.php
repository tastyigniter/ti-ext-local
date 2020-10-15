@foreach ($locationsList as $location)
    @php $object = $__SELF__->createLocationObject($location); @endphp
    <a
        class="card w-100 p-3 mb-2"
        href="{{ page_url('local/menus', ['location' => $location->permalink_slug]) }}"
    >
        <div class="boxes d-sm-flex no-gutters">
            <div class="col-12 col-sm-7">
                <div class="d-sm-flex">
                    @if ($location->hasMedia('thumb'))
                        <div class="col-sm-3 p-0 mr-sm-4 mb-3 mb-sm-0">
                            <img
                                class="img-responsive img-fluid"
                                src="{{ $location->getThumb() }}"
                            />
                        </div>
                    @endif
                    <dl class="no-spacing">
                        <dd><h2 class="h5 mb-0 text-body">{{ $location->location_name }}</h2></dd>
                        @if ($showReviews)
                            <dd>
                                <div class="rating rating-sm text-muted">
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star"></span>
                                    <span class="fa fa-star-half-o"></span>
                                    <span class="fa fa-star-o"></span>
                                    <span>{!! sprintf(lang('igniter.local::default.review.text_total_review'), $location->reviews_count) !!}</span>
                                </div>
                            </dd>
                        @endif
                        <dd class="d-none">
                        <span
                            class="text-muted text-truncate">{!! format_address($location->getAddress()) !!}</span>
                        </dd>
                        @if ($object->distance)
                            <dd>
                            <span
                                class="text-muted small"
                            ><i class="fa fa-map-marker"></i>&nbsp;&nbsp;{{ number_format($object->distance, 1) }} {{ $distanceUnit }}</span>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>
            <div class="col-12 col-sm-5">
                <dl class="no-spacing">
                    @if ($object->openingSchedule->isOpen())
                        <dt>@lang('igniter.local::default.text_is_opened')</dt>
                    @elseif ($object->openingSchedule->isOpening())
                        <dt class="text-muted">{!! sprintf(lang('igniter.local::default.text_opening_time'), $object->openingTime->isoFormat($openingTimeFormat)) !!}</dt>
                    @else
                        <dt class="text-muted">@lang('igniter.local::default.text_closed')</dt>
                    @endif
                    <dd class="text-muted">
                        @if ($object->hasDelivery)
                            @if ($object->deliverySchedule->isOpen())
                                {!! sprintf(lang('igniter.local::default.text_delivery_time_info'), sprintf(lang('igniter.local::default.text_in_minutes'), $object->deliveryMinutes)) !!}
                            @elseif ($object->deliverySchedule->isOpening())
                                {!! sprintf(lang('igniter.local::default.text_delivery_time_info'), '<span class="text-danger">'.sprintf(lang('igniter.local::default.text_starts'), $object->collectionTime->isoFormat($openingTimeFormat).'</span>')) !!}
                            @else
                                {!! sprintf(lang('igniter.local::default.text_delivery_time_info'), lang('igniter.local::default.text_is_closed')) !!}
                            @endif
                        @endif
                    </dd>
                    <dd class="text-muted">
                        @if ($object->hasCollection)
                            @if ($object->collectionSchedule->isOpen())
                                {!! sprintf(lang('igniter.local::default.text_collection_time_info'), sprintf(lang('igniter.local::default.text_in_minutes'), $object->collectionMinutes)) !!}
                            @elseif ($object->collectionSchedule->isOpening())
                                {!! sprintf(lang('igniter.local::default.text_collection_time_info'), '<span class="text-danger">'.sprintf(lang('igniter.local::default.text_starts'), $object->collectionTime->isoFormat($openingTimeFormat).'</span>')) !!}
                            @else
                                {!! sprintf(lang('igniter.local::default.text_collection_time_info'), lang('igniter.local::default.text_is_closed')) !!}
                            @endif
                        @endif
                    </dd>
                    <dd class="text-muted small">
                        @if (!$object->hasDelivery AND $object->hasCollection)
                            @lang('igniter.local::default.text_only_collection_is_available')
                        @elseif ($object->hasDelivery AND !$object->hasCollection)
                            @lang('igniter.local::default.text_only_delivery_is_available')
                        @elseif ($object->hasDelivery AND $object->hasCollection)
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