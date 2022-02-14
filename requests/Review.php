<?php

namespace Igniter\Local\Requests;

use System\Classes\FormRequest;

class Review extends FormRequest
{
    public function attributes()
    {
        return [
            'sale_type' => lang('igniter.local::default.reviews.label_sale_type'),
            'sale_id' => lang('igniter.local::default.reviews.label_sale_id'),
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
            'sale_type' => ['required'],
            'sale_id' => ['required', 'integer', 'saleIdExists'],
            'location_id' => ['required', 'integer'],
            'customer_id' => ['required', 'integer'],
            'quality' => ['required', 'integer', 'min:1', 'max:5'],
            'delivery' => ['required', 'integer', 'min:1', 'max:5'],
            'service' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['required', 'between:2,1028'],
            'review_status' => ['required', 'boolean'],
        ];
    }

    protected function prepareSaleIdExistsRule($parameters, $field)
    {
        return sprintf('exists:%s,%s_id', $this->inputWith('sale_type', ''), str_singular($this->inputWith('sale_type', '')));
    }
}
