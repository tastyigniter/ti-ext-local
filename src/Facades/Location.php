<?php

namespace Igniter\Local\Facades;

use Illuminate\Support\Facades\Facade;

class Location extends Facade
{
    /**
     * Get the registered name of the component.
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'location';
    }
}
