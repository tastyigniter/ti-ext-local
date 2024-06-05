<?php

namespace Igniter\Local\AutomationRules\Conditions;

use Igniter\Automation\AutomationException;
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
            'reviewable_id' => $object->getKey(),
            'reviewable_type' => $object->getMorphClass(),
        ])->count();
    }

    /**
     * Checks whether the condition is TRUE for specified parameters
     * @param array $params Specifies a list of parameters as an associative array.
     * @return bool
     */
    public function isTrue(&$params)
    {
        if (!$orderOrReservation = array_get($params, 'order', array_get($params, 'reservation'))) {
            throw new AutomationException('Error evaluating the review count condition: the order/reservation object is not found in the condition parameters.');
        }

        return $this->evalIsTrue($orderOrReservation);
    }
}
