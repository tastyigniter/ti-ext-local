<?php

namespace Igniter\Local\Classes;

use Igniter\Flame\Location\Contracts\AreaInterface;

/**
 * @method getLocationId()
 * @method getKey()
 * @method deliveryAmount($cartTotal)
 * @method minimumOrderTotal($cartTotal)
 * @method checkBoundary(\Igniter\Flame\Geolite\Contracts\CoordinatesInterface $userPosition)
 */
class CoveredArea
{
    protected $model;

    public function __construct(AreaInterface $model)
    {
        $this->model = $model;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function listConditions()
    {
        return collect($this->model->conditions ?? [])->mapInto(CoveredAreaCondition::class);
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