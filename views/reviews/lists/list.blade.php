{!! form_open([
    'id' => 'list-form',
    'role' => 'form',
    'method' => 'POST',
]) !!}

@if($recordCount = $this->records->count())
<div class="row px-3 mt-1 mb-3">
    @foreach (['quality', 'service', 'delivery'] as $ratingType)
    <div class="col">
        <div class="card bg-light shadow-sm">
            <div class="card-body">
                <h5>@lang('igniter.local::default.reviews.label_'.$ratingType)</h5>
                @for ($rating=5; $rating>0; $rating--)
                    @php $filteredRecords = $this->records->where($ratingType, $rating); @endphp
                <div class="row">
                    <div class="col-1">
                        <label>{{ $rating }}</label>
                    </div>
                    <div class="col-11 pt-1">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: {{ 100 * $filteredRecords->count() / $recordCount }}%">{{ $filteredRecords->count() }}</div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="list-table table-responsive">
    <table class="table table-striped mb-0 border-bottom">
        <thead>
        {!! $this->makePartial('lists/list_head') !!}
        </thead>
        <tbody>
        @if(count($records))
            {!! $this->makePartial('lists/list_body') !!}
        @else
            <tr>
                <td colspan="99" class="text-center">{{ $emptyMessage }}</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>

{!! form_close() !!}

{!! $this->makePartial('lists/list_pagination') !!}

@if ($showSetup)
    {!! $this->makePartial('lists/list_setup') !!}
@endif
