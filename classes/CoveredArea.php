<?php

namespace Igniter\Local\Classes;

use Igniter\Flame\Location\Contracts\AreaInterface;

/**
 * @method getLocationId()
 * @method getKey()
 * @method deliveryAmount($cartTotal)
 * @method minimumOrderTotal($cartTotal)
 * @method listConditions()
 * @method checkBoundary(\Igniter\Flame\Geolite\Contracts\CoordinatesInterface $userPosition)
 */
class CoveredArea
{
    protected $area;

    public function __construct(AreaInterface $area)
    {
        $this->area = $area;
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this->area, $method))
            return call_user_func_array([$this->area, $method], $parameters);
    }
}