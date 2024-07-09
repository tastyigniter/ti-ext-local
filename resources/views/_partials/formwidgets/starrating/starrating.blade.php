<div
    class="field-rating"
    data-control="star-rating"
    data-score="{{ $value }}"
    data-hints='@json($hints)'
    data-score-name="{{ $field->getName() }}"
    data-read-only="{{ $this->previewMode ? 'true' : 'false' }}"
    {!! $field->getAttributes() !!}>

    <div class="rating rating-star"></div>
</div>
