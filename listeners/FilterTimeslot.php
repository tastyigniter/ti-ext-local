<?php

namespace Igniter\Local\Listeners;

use Admin\Models\Orders_model;
use Carbon\Carbon;
use Igniter\Flame\Location\Models\AbstractLocation;
use Igniter\Local\Facades\Location;
use Illuminate\Contracts\Events\Dispatcher;

class FilterTimeslot
{
    protected static $ordersCache;

    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen('igniter.workingSchedule.timeslotFilter', __CLASS__.'@filterTimeslot');
    }

    public function filterTimeslot($workingSchedule, $timeslot)
    {
        // Skip if the working schedule is not for delivery or pickup
        if ($workingSchedule->getType() == AbstractLocation::OPENING)
            return;

        $ordersOnThisDay = $this->getOrders($timeslot->toDateString());
        // rest of the logic here
    }

    protected function getOrders($date)
    {
        if (!is_null(self::$ordersCache))
            return self::$ordersCache;

        $result = Orders_model::where('order_date', $date)
            ->where('location_id', Location::getId())
            ->whereIn('status_id', setting('processing_order_status', []))
            ->select(['order_time', 'order_date'])
            ->pluck('order_time', 'order_date');

        return self::$ordersCache = $result;
    }

    public function scheduleTimeslot()
    {
        // cache repeated calls
        if (!isset($this->scheduleCache)) {
            $this->scheduleCache = [
                'type' => '',
                'cache' => [],
            ];
        }

        if ($this->orderType() == $this->scheduleCache['type']) {
            return $this->scheduleCache['cache'];
        }

//        $schedule = $this->workingSchedule($this->orderType())->getTimeslot(
//            $this->orderTimeInterval(), null, $this->orderLeadTime()
//        );

        $self = $this;
        $schedule->each(function ($timeslots, $dateKey) use ($self, $schedule) {
            $limitTimeslots = $self->getModel()->getOption('limit_orders') ? $this->workingSchedule($this::OPENING)->getTimeslot(
                $this->getModel()->getOption('limit_orders_interval'), new DateTime($dateKey), 0
            )->toArray() : [];

            $ordersOnThisDay = $self->getModel()->getOption('limit_orders') ? Orders_model::where([
                ['location_id', '=', $this->getId()],
                ['order_date', '=', $dateKey],
                ['status_id', '!=', '0'],
            ])->select(['order_date', 'order_time'])->get() : [];

            $timeslots = $timeslots->filter(function ($item, $key) use ($self, $limitTimeslots, $dateKey, $ordersOnThisDay) {
                // allow the following logic to be overwritten by extensions
                if ($event = $self->fireSystemEvent('igniter.local.timeslotValid', [$item, $key, $dateKey])) {
                    return $event;
                }

                if (!$self->getModel()->getOption('limit_orders')) return TRUE;

                foreach ($limitTimeslots as $limitDate => $limitHoursArray) {
                    if ($limitDate == $dateKey) {
                        foreach ($limitHoursArray as $limitHours) {
                            $datetime = Carbon::parse($item);
                            $startTime = Carbon::parse($limitHours);
                            $endTime = Carbon::parse($limitHours)->addMinutes($this->getModel()->getOption('limit_orders_interval'));

                            if ($datetime->between($startTime, $endTime)) {
                                $orderCount = $ordersOnThisDay->filter(function ($order) use ($startTime, $endTime) {
                                    $orderTime = Carbon::createFromFormat('Y-m-d H:i:s', $order->order_date->format('Y-m-d').' '.$order->order_time);

                                    return $orderTime->between(
                                        $startTime,
                                        $endTime
                                    );
                                });

                                return $orderCount->count() < $self->getModel()->getOption('limit_orders_count');
                            }
                        }
                    }
                }

                return FALSE;
            });

            $schedule->put($dateKey, $timeslots);
        });

        $this->scheduleCache['type'] = $this->orderType();
        $this->scheduleCache['cache'] = $schedule;

        return $schedule;
    }
}