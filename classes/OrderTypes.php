<?php

namespace Igniter\Local\Classes;

use Igniter\Flame\Traits\Singleton;
use System\Classes\ExtensionManager;

class OrderTypes
{
    use Singleton;

    /**
     * @var array An array of registered order types.
     */
    protected $orderTypes = [];

    /**
     * @var array An array of registered order types.
     */
    protected $registeredOrderTypes;

    /**
     * @var array Cache of order types registration callbacks.
     */
    protected $registeredCallbacks = [];

    protected function initialize()
    {
        $this->loadOrderTypes();
    }

    public function hasOrderType($code)
    {
        return array_has($this->registeredOrderTypes, $code);
    }

    /**
     * @param $code
     * @return \Igniter\Local\Classes\BaseOrderType
     */
    public function getOrderType($code)
    {
        return array_get($this->registeredOrderTypes, $code);
    }

    public function getOrderTypes()
    {
        return $this->registeredOrderTypes;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getOrderTypesWith($location)
    {
        return collect($this->registeredOrderTypes)
            ->map(function ($className, $code) use ($location) {
                return new $className($location, $code);
            });
    }

    public function registerOrderType($className, $code)
    {
        if ($this->registeredOrderTypes === null)
            $this->registeredOrderTypes = [];

        $this->registeredOrderTypes[$code] = $className;
    }

    public function registerCallback(callable $definitions)
    {
        $this->registeredCallbacks[] = $definitions;
    }

    protected function loadOrderTypes()
    {
        foreach ($this->registeredCallbacks as $callback) {
            $callback($this);
        }

        // Load extensions order types
        $registeredOrderTypes = ExtensionManager::instance()->getRegistrationMethodValues('registerOrderTypes');
        foreach ($registeredOrderTypes as $orderTypes) {
            foreach ($orderTypes as $className => $code) {
                $this->registerOrderType($className, $code);
            }
        }
    }
}
