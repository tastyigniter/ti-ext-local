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
        if (!$object instanceof Model)
            return false;

        return Reviews_model::query()->where([
            'sale_id' => $object->getKey(),
            'sale_type' => $object->getMorphClass(),
        ])->count();
    }
}
