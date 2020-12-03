<?php

namespace Igniter\Local\Requests;

use System\Classes\FormRequest;

class Review extends FormRequest
{
    public function rules()
    {
        return [
            ['sale_type', 'igniter.local::default.reviews.label_sale_type', 'required'],
            ['sale_id', 'igniter.local::default.reviews.label_sale_id', 'required|integer|saleIdExists'],
            ['location_id', 'igniter.local::default.reviews.label_location', 'required|integer'],
            ['customer_id', 'igniter.local::default.reviews.label_customer', 'required|integer'],
            ['quality', 'igniter.local::default.reviews.label_quality', 'required|integer|min:1'],
            ['delivery', 'igniter.local::default.reviews.label_delivery', 'required|integer|min:1'],
            ['service', 'igniter.local::default.reviews.label_service', 'required|integer|min:1'],
            ['review_text', 'igniter.local::default.reviews.label_text', 'required|between:2,1028'],
            ['review_status', 'admin::lang.label_status', 'required|boolean'],
        ];
    }

    protected function prepareSaleIdExistsRule($parameters, $field)
    {
        return sprintf('exists:%s,%s_id', $this->inputWith('sale_type', ''), str_singular($this->inputWith('sale_type', '')));
    }
}
