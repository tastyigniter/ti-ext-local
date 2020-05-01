<?php

namespace Igniter\Local\Classes;

use Igniter\Flame\Location\Contracts\AreaInterface;

/**
 * @method getLocationId()
 * @method getKey()
 * @method checkBoundary(\Igniter\Flame\Geolite\Contracts\CoordinatesInterface $userPosition)
 */
class CoveredArea
{
    protected $model;

    public function __construct(AreaInterface $model)
    {
        $this->model = $model;
    }

    public function deliveryAmount($cartTotal)
    {
        return $this->getConditionValue('amount', $cartTotal);
    }

    public function minimumOrderTotal($cartTotal)
    {
        return $this->getConditionValue('total', $cartTotal);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function listConditions()
    {
        return collect($this->model->conditions ?? [])
            ->sortBy('priority')
            ->mapInto(CoveredAreaCondition::class);
    }

    protected function getConditionValue($type, $cartTotal)
    {
        if (!$condition = $this->checkConditions($cartTotal, $type))
            return null;

        // Delivery is unavailable when delivery charge from the matched rule is -1
        if ($condition->amount < 0)
            return $type == 'total' ? $condition->total : null;

        // At this stage, minimum total is 0 when the matched condition is a below rule
        if ($type == 'total' AND $condition->type == 'below')
            return 0;

        return $condition->{$type};
    }

    protected function checkConditions($cartTotal, $value = 'total')
    {
        return $this->listConditions()->first(function (CoveredAreaCondition $condition) use ($cartTotal) {
            return $condition->isValid($cartTotal);
        });
    }

    public function __get($key)
    {
        return $this->model->getAttribute($key);
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this->model, $method))
            return call_user_func_array([$this->model, $method], $parameters);
    }
}