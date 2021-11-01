@if (count($locationInfo->schedules))
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th></th>
                <th>@lang('igniter.local::default.text_opening')</th>
                <th>@lang('igniter.local::default.text_delivery')</th>
                <th>@lang('igniter.local::default.text_collection')</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($locationInfo->schedules as $day => $schedules)
                <tr>
                    <td>{{ $day }}</td>
                    @foreach ($schedules->sortByDesc('type')->groupBy('type') as $type => $hours)
                        <td>
                            @foreach ($hours as $hour)
                                @if ($type == 'delivery' && !$locationInfo->hasDelivery)
                                    @lang('igniter.local::default.text_closed')
                                @elseif ($type == 'collection' && !$locationInfo->hasCollection)
                                    @lang('igniter.local::default.text_closed')
                                @elseif (!$hour->isEnabled())
                                    @lang('igniter.local::default.text_closed')
                                @elseif ($hour->isOpenAllDay())
                                    @lang('igniter.local::default.text_24h')
                                @else
                                    {!! sprintf(
                                        lang('igniter.local::default.text_working_hour'),
                                        $hour->open->isoFormat($infoTimeFormat),
                                        $hour->close->isoFormat($infoTimeFormat)
                                    ) !!}{{ $loop->last ? '' : ', ' }}
                                @endif
                            @endforeach
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
