<?php

namespace Igniter\Local\Http\Requests;

use Igniter\System\Classes\FormRequest;

class ReviewRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'reviewable_type' => lang('igniter.local::default.reviews.label_reviewable_type'),
            'reviewable_id' => lang('igniter.local::default.reviews.label_reviewable_id'),
            'location_id' => lang('igniter.local::default.reviews.label_location'),
            'customer_id' => lang('igniter.local::default.reviews.label_customer'),
            'quality' => lang('igniter.local::default.reviews.label_quality'),
            'delivery' => lang('igniter.local::default.reviews.label_delivery'),
            'service' => lang('igniter.local::default.reviews.label_service'),
            'review_text' => lang('igniter.local::default.reviews.label_text'),
            'review_status' => lang('admin::lang.label_status'),
        ];
    }

    public function rules()
    {
        return [
            'reviewable_type' => ['required'],
            'reviewable_id' => ['required', 'integer', sprintf('exists:%s,%s_id', $this->reviewable_type, str_singular($this->reviewable_type))],
            'location_id' => ['required', 'integer'],
            'customer_id' => ['required', 'integer'],
            'quality' => ['required', 'integer', 'min:1', 'max:5'],
            'delivery' => ['required', 'integer', 'min:1', 'max:5'],
            'service' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['required', 'between:2,1028'],
            'review_status' => ['required', 'boolean'],
        ];
    }
}
