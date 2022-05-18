<div class="row mx-auto px-3 my-3">
    @foreach (['quality', 'service', 'delivery'] as $ratingType)
        <div @class(['col-md-4', 'ps-md-0' => $loop->first, 'pe-md-0' => $loop->last])">
        <div class="card bg-light shadow-sm">
            <div class="card-body">
                <h5 class="text-muted">@lang('igniter.local::default.reviews.label_'.$ratingType)</h5>
                <div
                    class="chart-container pt-3"
                    data-control="review-chart"
                    data-data='@json($this->getController()->makeAverageRatingDataset($ratingType, $this->records))'
                >
                    <div class="chart-canvas">
                        <canvas
                            style="width: 100%; height: 128px"
                        ></canvas>
                    </div>
                </div>
            </div>
        </div>
</div>
@endforeach
</div>
