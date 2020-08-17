<?php

namespace Igniter\Local\Listeners;

use Admin\Models\Orders_model;
use Carbon\Carbon;
use DateTime;
use Igniter\Flame\Location\Models\AbstractLocation;
use Igniter\Local\Classes\Location;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Flame\Traits\EventEmitter;
use Illuminate\Contracts\Events\Dispatcher;

class FilterTimeslot
{
    use EventEmitter;

    protected static $ordersCache = [];
    protected static $scheduleCache = [];

    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen('igniter.workingSchedule.timeslotFilter', __CLASS__.'@filterTimeslot');
    }

    public function filterTimeslot($workingSchedule, $timeslot)
    {
        // Skip if the working schedule is not for delivery or pickup
        if ($workingSchedule->getType() == AbstractLocation::OPENING)
            return;
            
        // allow the timeslot selection logic to be overwritten by extensions
        if ($event = $this->fireSystemEvent('igniter.local.timeslotValid', [$timeslot])) {
            return $event;
        }
            
        $dateString = Carbon::parse($timeslot)->toDateString();
            
        $ordersOnThisDay = $this->getOrders($dateString);
                
        $location = new Location(LocationFacade::getId());
        $limitTimeslots = $this->getSchedule($location, $dateString);

        foreach ($limitTimeslots as $limitDate => $limitHoursArray)
        {
            if ($limitDate == $dateString)
            {
                foreach ($limitHoursArray as $limitHours)
                {
                    $datetime = Carbon::parse($timeslot);
                    $startTime = Carbon::parse($limitHours);
                    $endTime = Carbon::parse($limitHours)->addMinutes($location->getModel()->getOption('limit_orders_interval'));

                    if ($datetime->between($startTime, $endTime))
                    {
                        $orderCount = $ordersOnThisDay->filter(function ($order) use ($startTime, $endTime) {
                            $orderTime = Carbon::createFromFormat('Y-m-d H:i:s', $order->order_date->format('Y-m-d').' '.$order->order_time);

                            return $orderTime->between(
                                $startTime,
                                $endTime
                            );
                        });

                        return $orderCount->count() < $location->getModel()->getOption('limit_orders_count');
                    }
                }
            }
        }
        
        return false;

    }

    protected function getOrders($date)
    {
        if (array_has(self::$ordersCache, $date))
            return self::$ordersCache[$date];
            
        $result = Orders_model::where('order_date', $date)
            ->where('location_id', LocationFacade::getId())
            ->whereIn('status_id', array_merge(setting('processing_order_status', []), setting('completed_order_status', [])))
            ->select(['order_time', 'order_date'])
            ->pluck('order_time', 'order_date');

        return self::$ordersCache[$date] = $result;
    }
    
    protected function getSchedule($location, $date)
    {
        if (array_has(self::$scheduleCache, $date))
            return self::$scheduleCache[$date];

        $schedule = $location->getModel()->getOption('limit_orders') ? $location->workingSchedule($location::OPENING)->getTimeslot(
            $location->getModel()->getOption('limit_orders_interval'), new DateTime($date), 0
        )->toArray() : [];
        
        return self::$scheduleCache[$date] = $schedule;
    }
}