<?php

namespace Igniter\Local\Facades;

use Illuminate\Support\Facades\Facade;

class AdminLocation extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     * @see \Igniter\User\Auth\UserGuard
     */
    protected static function getFacadeAccessor()
    {
        return 'admin.location';
    }
}
