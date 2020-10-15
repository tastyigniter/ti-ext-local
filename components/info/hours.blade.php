@if (count($locationInfo->schedules))
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
            <tr>
                <th></th>
                <th>@lang('igniter.local::default.text_opening')</th>
                <th>@lang('igniter.local::default.text_delivery')</th>
                <th>@lang('igniter.local::default.text_collection')</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($locationInfo->schedules as $day => $hours)
                <tr>
                    <td>{{ $day }}</td>
                    @foreach ($hours->sortByDesc('type') as $hour)
                        @if ($hour->type == 'delivery' AND !$locationInfo->hasDelivery)
                            <td>@lang('igniter.local::default.text_closed')</td>
                        @elseif ($hour->type == 'collection' AND !$locationInfo->hasCollection)
                            <td>@lang('igniter.local::default.text_closed')</td>
                        @elseif (!$hour->isEnabled())
                            <td>@lang('igniter.local::default.text_closed')</td>
                        @elseif ($hour->isOpenAllDay())
                            <td>@lang('igniter.local::default.text_24h')</td>
                        @else
                            <td>{!! sprintf(
                                lang('igniter.local::default.text_working_hour'),
                                $hour->open->isoFormat($infoTimeFormat),
                                $hour->close->isoFormat($infoTimeFormat)
                            ) !!}</td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
