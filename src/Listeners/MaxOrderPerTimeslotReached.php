<?php

declare(strict_types=1);

namespace Igniter\Local\Listeners;

use Carbon\Carbon;
use DateTimeInterface;
use Igniter\Cart\Models\Order;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Classes\WorkingSchedule;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Local\Models\Location;
use Illuminate\Contracts\Events\Dispatcher;

class MaxOrderPerTimeslotReached
{
    public static $ordersCache = [];

    public function subscribe(Dispatcher $dispatcher): void
    {
        $dispatcher->listen('igniter.workingSchedule.timeslotValid', __CLASS__.'@timeslotValid');

        $dispatcher->listen('igniter.checkout.beforeSaveOrder', __CLASS__.'@beforeSaveOrder');
    }

    public function timeslotValid(WorkingSchedule $workingSchedule, DateTimeInterface $timeslot): ?bool
    {
        // Skip if the working schedule is not for delivery or pickup
        if ($workingSchedule->getType() === Location::OPENING) {
            return null;
        }

        if ($this->execute($timeslot, $workingSchedule->getType())) {
            return false;
        }

        return null;
    }

    public function beforeSaveOrder(Order $order, array $data): void
    {
        if ($this->execute($order->order_datetime, $order->order_type)) {
            throw new ApplicationException(lang('igniter.local::default.alert_max_guest_reached'));
        }
    }

    protected function execute(DateTimeInterface $timeslot, string $orderType): ?bool
    {
        $locationModel = LocationFacade::current();
        if (!(bool)$locationModel?->getSettings('checkout.limit_orders')) {
            return null;
        }

        $ordersOnThisDay = $this->getOrders($timeslot);
        if ($ordersOnThisDay->isEmpty()) {
            return null;
        }

        $startTime = Carbon::parse($timeslot);
        $endTime = Carbon::parse($timeslot)->addMinutes($locationModel->getOrderTimeInterval($orderType))->subMinute();

        $orderCount = $ordersOnThisDay->filter(function(string $time) use ($startTime, $endTime): bool {
            $orderTime = Carbon::createFromFormat('Y-m-d H:i:s', $startTime->format('Y-m-d').' '.$time);

            return $orderTime->between($startTime, $endTime);
        })->count();

        return $orderCount >= (int)$locationModel->getSettings('checkout.limit_orders_count', 50);
    }

    protected function getOrders(DateTimeInterface $timeslot)
    {
        $date = Carbon::parse($timeslot)->toDateString();

        if (array_has(self::$ordersCache, $date)) {
            return self::$ordersCache[$date];
        }

        $result = Order::where('order_date', $date)
            ->where('location_id', LocationFacade::getId())
            ->whereIn('status_id', array_merge([setting('default_order_status', -1)], setting('processing_order_status', []), setting('completed_order_status', [])))
            ->select('order_time')
            ->pluck('order_time');

        return self::$ordersCache[$date] = $result;
    }
}
