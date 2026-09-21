<?php

declare(strict_types=1);

namespace Igniter\Local\Http\Requests;

use Igniter\Local\Models\Review;
use Igniter\System\Classes\FormRequest;
use Override;

class ReviewRequest extends FormRequest
{
    #[Override]
    public function attributes(): array
    {
        return [
            'reviewable_type' => lang('igniter.local::default.reviews.label_reviewable_type'),
            'reviewable_id' => lang('igniter.local::default.reviews.label_reviewable_id'),
            'location_id' => lang('igniter.local::default.reviews.label_location'),
            'author' => lang('igniter.local::default.reviews.label_customer'),
            'quality' => lang('igniter.local::default.reviews.label_quality'),
            'delivery' => lang('igniter.local::default.reviews.label_delivery'),
            'service' => lang('igniter.local::default.reviews.label_service'),
            'review_text' => lang('igniter.local::default.reviews.label_text'),
            'review_status' => lang('admin::lang.label_status'),
        ];
    }

    public function rules(): array
    {
        $reviewableType = $this->reviewable_type;
        $modelClass = is_string($reviewableType)
            ? (Review::$relatedSaleTypes[$reviewableType] ?? null)
            : null;

        $reviewableIdRules = ['required', 'integer'];
        if (is_string($modelClass)) {
            $model = new $modelClass;
            $reviewableIdRules[] = sprintf('exists:%s,%s', $model->getTable(), $model->getKeyName());
        }

        return [
            'reviewable_type' => ['required', 'in:'.implode(',', array_keys(Review::$relatedSaleTypes))],
            'reviewable_id' => $reviewableIdRules,
            'location_id' => ['required', 'integer'],
            'author' => ['sometimes', 'required', 'string', 'between:2,255'],
            'quality' => ['required', 'integer', 'min:1', 'max:5'],
            'delivery' => ['required', 'integer', 'min:1', 'max:5'],
            'service' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['required', 'between:2,1028'],
            'review_status' => ['required', 'boolean'],
        ];
    }
}
