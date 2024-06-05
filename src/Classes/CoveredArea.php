<?php

namespace Igniter\Local\Classes;

use Igniter\Local\Contracts\AreaInterface;
use Igniter\Local\Facades\Location;

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
        return $this->getConditionValue('amount', $cartTotal) + $this->calculateDistanceCharges();
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

    public function getConditionLabels()
    {
        return $this->listConditions()->map(function(CoveredAreaCondition $condition) {
            return ucfirst(strtolower($condition->getLabel()));
        })->all();
    }

    protected function getConditionValue($type, $cartTotal)
    {
        if (!$condition = $this->checkConditions($cartTotal, $type)) {
            return 0;
        }

        // Delivery is unavailable when delivery charge from the matched rule is -1
        if ($condition->amount < 0) {
            return $type == 'total' ? $condition->total : -1;
        }

        // At this stage, return the minimum total when the matched condition is a below
        if ($type == 'total' && $condition->type == 'below') {
            return $condition->total;
        }

        return $condition->{$type};
    }

    protected function checkConditions($cartTotal, $value = 'total')
    {
        return $this->listConditions()->first(function(CoveredAreaCondition $condition) use ($cartTotal) {
            return $condition->isValid($cartTotal);
        });
    }

    protected function calculateDistanceCharges()
    {
        $distanceCharges = collect($this->model->boundaries['distance'] ?? []);

        if (!Location::userPosition()->isValid() || $distanceCharges->isEmpty()) {
            return 0;
        }

        $distanceFromLocation = round(Location::checkDistance(), 2);

        $condition = $distanceCharges
            ->sortBy('priority')
            ->map(function($condition) {
                return new CoveredAreaCondition([
                    'type' => $condition['type'],
                    'amount' => $condition['charge'],
                    'total' => $condition['distance'],
                ]);
            })
            ->first(function(CoveredAreaCondition $condition) use ($distanceFromLocation) {
                return $condition->isValid($distanceFromLocation);
            });

        return optional($condition)->amount ?? 0;
    }

    public function __get($key)
    {
        return $this->model->getAttribute($key);
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this->model, $method)) {
            return call_user_func_array([$this->model, $method], $parameters);
        }
    }
}
