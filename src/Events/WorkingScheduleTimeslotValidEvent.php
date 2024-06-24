<?php

namespace Igniter\Local\Events;

use DateTimeInterface;
use Igniter\Local\Classes\WorkingSchedule;

class WorkingScheduleTimeslotValidEvent
{
    use \Igniter\Flame\Traits\EventDispatchable;

    public function __construct(public WorkingSchedule $schedule, public DateTimeInterface $timeslot) {}

    public static function eventName()
    {
        return 'admin.workingSchedule.timeslotValid';
    }
}
