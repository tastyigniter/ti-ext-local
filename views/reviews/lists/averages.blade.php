@if($recordCount = $this->records->count())
@php $hints = \Igniter\Local\Models\Reviews_model::make()->getRatingOptions(); @endphp
<div class="row px-3 mt-1 mb-3">
    @foreach (['quality', 'service', 'delivery'] as $ratingType)
    <div class="col">
        <div class="card bg-light shadow-sm">
            <div class="card-body">
                <h5>@lang('igniter.local::default.reviews.label_'.$ratingType)</h5>
                @for ($rating=5; $rating>0; $rating--)
                    @php 
                        $filteredRecords = $this->records->where($ratingType, $rating); 
                        $ratingHint = $hints[$rating] ?? $rating;
                    @endphp
                <div class="row">
                    <div class="col-1">
                        <label>{{ $ratingHint }}</label>
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