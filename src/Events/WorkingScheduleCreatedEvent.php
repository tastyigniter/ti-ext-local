<?php

namespace Igniter\Local\Events;

use Igniter\Flame\Database\Model;
use Igniter\Flame\Traits\EventDispatchable;
use Igniter\Local\Classes\WorkingSchedule;

class WorkingScheduleCreatedEvent
{
    use EventDispatchable;

    public function __construct(public Model $model, public WorkingSchedule $schedule) {}

    public static function eventName()
    {
        return 'admin.workingSchedule.created';
    }
}
