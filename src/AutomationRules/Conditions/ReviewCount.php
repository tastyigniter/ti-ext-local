<?php

namespace Igniter\Local\AutomationRules\Conditions;

use Igniter\Automation\Classes\BaseModelAttributesCondition;
use Igniter\Cart\Models\Order;
use Igniter\Local\Models\Review;
use Igniter\Reservation\Models\Reservation;

class ReviewCount extends BaseModelAttributesCondition
{
    protected $modelClass = \Igniter\Local\Models\Review::class;

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
        if (!$object instanceof Order && !$object instanceof Reservation) {
            return false;
        }

        return Review::query()->where([
            'sale_id' => $object->getKey(),
            'sale_type' => $object->getMorphClass(),
        ])->count();
    }
}
