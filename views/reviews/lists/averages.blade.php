@if($recordCount = $this->records->count())
@php 
    $hints = \Igniter\Local\Models\Reviews_model::make()->getRatingOptions(); 
    $pieColors = ['#4DB6AC', '#64B5F6', '#BA68C8'];
@endphp
<div class="row px-3 mt-1 mb-3">
    @foreach (['quality', 'service', 'delivery'] as $ratingType)
    <div class="col">
        <div class="card bg-light shadow-sm">
            <div class="card-body">
                <h5>@lang('igniter.local::default.reviews.label_'.$ratingType)</h5>
                @for ($rating = 5; $rating > 0; $rating--)
                    @php 
                        $chartData = [
                			'datasets' => [
                        		[
                					'data' => [],
                					'backgroundColor' => [],
                				],
                			],
                			'labels' => [],
                		];
                        
                        for ($rating=5; $rating>0; $rating--) {
                            $chartData['datasets'][0]['data'][] = $this->records->where($ratingType, $rating)->count(); 
                            $chartData['datasets'][0]['backgroundColor'][] = $pieColors[$rating % count($pieColors)];
                            $chartData['labels'][] = $hints[$rating] ?? $rating;
                        }
                    @endphp
                @endfor
                <div
                    class="chart-container"
                    data-control="review-chart"
                >
                    <div class="chart-canvas">
            			<textarea style="display:none;">{!! json_encode($chartData) !!}</textarea>
                        <canvas
            				style="width: 100%; height: 200px"
                        ></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif