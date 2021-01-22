<?php

namespace Igniter\Local\AutomationRules\Conditions;

use Igniter\Automation\Classes\BaseModelAttributesCondition;
use Igniter\Local\Models\Reviews_model;
use Illuminate\Database\Eloquent\Model;

class ReviewCount extends BaseModelAttributesCondition
{
    protected $modelClass = \Igniter\Local\Models\Reviews_model::class;

    protected $modelAttributes;

    public function conditionDetails()
    {
        return [
            'name' => 'Review Count',
            'description' => 'Number of reviews for this order or reservation',
        ];
    }

    public function defineModelAttributes()
    {
        return [
            'review_count' => [
                'label' => 'Number of reviews',
            ],
        ];
    }

    public function getReviewCountAttribute($value, $object)
    {
        $object = array_get($params, 'order', array_get($params, 'reservation'));
        if (!$object instanceof Model)
            return false;

        return Reviews_model::where([
            'sale_id' => $object->order_id ?? $object->reservation_id,
            'sale_type' => $object->order_id ? 'orders' : 'reservations',
        ])->count();
    }
}
