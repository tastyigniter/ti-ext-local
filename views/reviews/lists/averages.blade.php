@if($recordCount = $this->records->count())
@php $hints = \Igniter\Local\Models\Reviews_model::make()->getRatingOptions(); @endphp
<div class="row px-3 mt-1 mb-3">
    @foreach (['quality', 'service', 'delivery'] as $ratingType)
    <div class="col">
        <div class="card bg-light shadow-sm">
            <div class="card-body">
                <h5>@lang('igniter.local::default.reviews.label_'.$ratingType)</h5>
                @for ($rating = 5; $rating > 0; $rating--)
                    @php 
                        $filteredRecords = $this->records->where($ratingType, $rating);
                        $ratingHint = $hints[$rating] ?? $rating;
                    @endphp
                <div class="progress mt-1" style="height:24px;">
                    <div class="progress-bar" role="progressbar" style="width: {{ 100 * $filteredRecords->count() / $recordCount }}%"><span class="h6 pt-2">{{ $ratingHint }} ({{ $filteredRecords->count() }})</span></div>
                </div>
                @endfor
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif
