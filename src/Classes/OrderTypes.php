<?php

namespace Igniter\Local\Classes;

use Igniter\System\Classes\ExtensionManager;

class OrderTypes
{
    /**
     * @var array An array of registered order types.
     */
    protected $registeredOrderTypes = [];

    /**
     * @var array Cache of order types registration callbacks.
     */
    protected static $registeredCallbacks = [];

    public function __construct()
    {
        $this->loadOrderTypes();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function makeOrderTypes($location)
    {
        return collect($this->registeredOrderTypes)
            ->map(function ($orderType) use ($location) {
                return new $orderType['className']($location, $orderType);
            });
    }

    /**
     * @return \Igniter\Local\Classes\AbstractOrderType
     */
    public function findOrderType($code)
    {
        return array_get($this->registeredOrderTypes, $code);
    }

    public function listOrderTypes()
    {
        return $this->registeredOrderTypes;
    }

    public function loadOrderTypes()
    {
        foreach (self::$registeredCallbacks as $callback) {
            $callback($this);
        }

        $registeredConditions = resolve(ExtensionManager::class)->getRegistrationMethodValues('registerScheduleTypes');
        foreach ($registeredConditions as $extensionCode => $orderTypes) {
            $this->registerOrderTypes($orderTypes);
        }
    }

    public function registerOrderTypes($orderTypes)
    {
        foreach ($orderTypes as $className => $definition) {
            $this->registerOrderType($className, $definition);
        }
    }

    public function registerOrderType($className, $definition)
    {
        $code = $definition['code'] ?? strtolower(basename($className));

        if (!array_key_exists('name', $definition)) {
            $definition['name'] = $code;
        }

        $this->registeredOrderTypes[$code] = array_merge($definition, [
            'className' => $className,
        ]);
    }

    public static function registerCallback(callable $definitions)
    {
        self::$registeredCallbacks[] = $definitions;
    }
}
