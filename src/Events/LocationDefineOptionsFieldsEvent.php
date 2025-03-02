<?php

namespace Igniter\Local\Events;

use Igniter\Flame\Traits\EventDispatchable;

class LocationDefineOptionsFieldsEvent
{
    use EventDispatchable;

    public static function eventName()
    {
        return 'admin.location.defineOptionsFormFields';
    }
}
