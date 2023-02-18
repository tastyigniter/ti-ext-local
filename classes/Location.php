<?php

namespace Igniter\Local\Classes;

use Admin\Facades\AdminAuth;
use Admin\Models\Location_areas_model;
use Admin\Models\Locations_model;
use Carbon\Carbon;
use Igniter\Flame\Geolite\Model\Distance;
use Igniter\Flame\Geolite\Model\Location as UserLocation;
use Igniter\Flame\Location\AbstractOrderType;
use Igniter\Flame\Location\Contracts\AreaInterface;
use Igniter\Flame\Location\Manager;

/**
 * Location Class
 */
class Location extends Manager
{
    const CLOSED = 'closed';

    const OPEN = 'open';

    const OPENING = 'opening';

    protected $locationModel = \Admin\Models\Locations_model::class;

    /**
     * @var \Igniter\Local\Classes\CoveredArea
     */
    protected $coveredArea;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $orderTypes;

    protected $scheduleCache = [];

    protected $distanceCache;

    public function __construct()
    {
        $this->setDefaultLocation(params('default_location_id'));

        $this->locationSlugResolver(function () {
            return controller()->param('location');
        });
    }

    //
    // BOOT METHODS
    //

    public function updateNearbyArea(AreaInterface $area)
    {
        $this->setCurrent($area->location);

        $this->setCoveredArea(new CoveredArea($area));
    }

    public function updateOrderType($code = null)
    {
        $oldOrderType = $this->getSession('orderType');

        if (is_null($code))
            $this->forgetSession('orderType');

        if (strlen($code)) {
            $this->forgetSession('order-timeslot');

            $this->putSession('orderType', $code);
            $this->fireSystemEvent('location.orderType.updated', [$code, $oldOrderType]);
        }
    }

    public function updateUserPosition(UserLocation $position)
    {
        $oldPosition = $this->getSession('position');

        $this->putSession('position', $position);

        $this->clearCoveredArea();

        $this->fireSystemEvent('location.position.updated', [$position, $oldPosition]);
    }

    public function updateScheduleTimeSlot($dateTime, $isAsap = true)
    {
        $oldSlot = $this->getSession('order-timeslot');

        $slot['dateTime'] = (!$isAsap && !is_null($dateTime)) ? make_carbon($dateTime) : null;
        $slot['isAsap'] = $isAsap;

        if (!$slot) {
            $this->forgetSession('order-timeslot');
        }
        else {
            $this->putSession('order-timeslot', $slot);
        }

        $this->fireSystemEvent('location.timeslot.updated', [$slot, $oldSlot]);
    }

    /**
     * Extend the query used for finding the location.
     *
     * @param \Igniter\Flame\Database\Builder $query
     *
     * @return void
     */
    public function extendLocationQuery($query)
    {
        if (!optional(AdminAuth::getUser())->hasPermission('Admin.Locations'))
            $query->IsEnabled();
    }

    //
    // HELPER METHODS
    //

    public function getId()
    {
        return $this->getModel()->getKey();
    }

    public function orderType()
    {
        return $this->getSession('orderType', Locations_model::DELIVERY);
    }

    /**
     * @return \Igniter\Flame\Geolite\Model\Location
     */
    public function userPosition()
    {
        return $this->getSession('position', UserLocation::createFromArray([]));
    }

    public function requiresUserPosition()
    {
        return setting('location_order') == 1;
    }

    public function checkOrderType($code = null)
    {
        return !$this->getOrderType($code)->isDisabled();
    }

    public function orderTypeIsDelivery()
    {
        return $this->orderType() === Locations_model::DELIVERY;
    }

    public function orderTypeIsCollection()
    {
        return $this->orderType() === Locations_model::COLLECTION;
    }

    public function hasOrderType($code = null)
    {
        if (!$orderType = $this->getOrderType($code))
            return false;

        return !$orderType->isDisabled();
    }

    /**
     * @param null $code
     * @return \Igniter\Flame\Location\AbstractOrderType
     */
    public function getOrderType($code = null)
    {
        $code = !is_null($code) ? $code : $this->orderType();

        return $this->getOrderTypes()->get($code);
    }

    public function getOrderTypes()
    {
        if ($this->orderTypes)
            return $this->orderTypes;

        return $this->orderTypes = $this->getModel()->availableOrderTypes();
    }

    public function minimumOrderTotal($orderType = null)
    {
        return $this->getOrderType($orderType)->getMinimumOrderTotal();
    }

    public function checkMinimumOrderTotal($cartTotal, $orderType = null)
    {
        return $cartTotal >= $this->minimumOrderTotal($orderType);
    }

    //
    // HOURS
    //

    public function openingSchedule()
    {
        return $this->workingSchedule(Locations_model::OPENING);
    }

    public function deliverySchedule()
    {
        return $this->workingSchedule(Locations_model::DELIVERY);
    }

    public function collectionSchedule()
    {
        return $this->workingSchedule(Locations_model::COLLECTION);
    }

    public function isOpened()
    {
        return $this->getOrderType()->getSchedule()->isOpen();
    }

    public function isClosed()
    {
        return $this->getOrderType()->getSchedule()->isClosed();
    }

    public function openTime($type = null, $format = null)
    {
        if (is_null($type))
            $type = $this->orderType();

        return $this->workingSchedule($type)->getOpenTime($format);
    }

    public function closeTime($type = null, $format = null)
    {
        if (is_null($type))
            $type = $this->orderType();

        return $this->workingSchedule($type)->getCloseTime($format);
    }

    protected function workingStatus($type = null, $timestamp = null)
    {
        if (is_null($type))
            $type = $this->orderType();

        return $this->workingSchedule($type)->checkStatus($timestamp);
    }

    //
    // Timeslot
    //

    public function orderTimeInterval()
    {
        return $this->getOrderType()->getInterval();
    }

    public function lastOrderTime()
    {
        return Carbon::parse($this->getOrderType()->getSchedule()->getCloseTime());
    }

    public function orderLeadTime()
    {
        return $this->getOrderType()->getLeadTime();
    }

    public function orderTimeIsAsap()
    {
        if (!$this->hasAsapSchedule())
            return false;

        $dateTime = $this->getSession('order-timeslot.dateTime');
        $orderTimeIsAsap = (bool)$this->getSession('order-timeslot.isAsap', true);

        if (!$this->isOpened())
            return false;

        return $orderTimeIsAsap || ($dateTime && now()->isAfter($dateTime));
    }

    /**
     * @return \Carbon\Carbon
     */
    public function orderDateTime()
    {
        $dateTime = $this->getSession('order-timeslot.dateTime');
        if ($this->orderTimeIsAsap())
            $dateTime = $this->asapScheduleTimeslot();

        if (!$dateTime || now()->isAfter($dateTime)) {
            $dateTime = $this->hasAsapSchedule()
                ? $this->asapScheduleTimeslot()
                : $this->firstScheduleTimeslot();
        }

        return make_carbon($dateTime);
    }

    public function scheduleTimeslot($orderType = null)
    {
        if (is_null($orderType))
            $orderType = $this->orderType();

        if (array_key_exists($orderType, $this->scheduleCache))
            return $this->scheduleCache[$orderType];

        $leadMinutes = $this->model->getOption($orderType.'_add_lead_time')
            ? $this->orderLeadTime() : 0;

        $result = $this->getOrderType($orderType)->getSchedule()->getTimeslot(
            $this->orderTimeInterval(), null, $leadMinutes
        );

        return $this->scheduleCache[$orderType] = $result;
    }

    public function firstScheduleTimeslot()
    {
        return $this->scheduleTimeslot()->collapse()->first();
    }

    public function asapScheduleTimeslot()
    {
        if ($this->isClosed() || (bool)$this->getModel()->getOption('limit_orders'))
            return $this->firstScheduleTimeslot();

        return Carbon::now();
    }

    public function checkOrderTime($timestamp = null, $orderTypeCode = null)
    {
        if (is_null($timestamp))
            $timestamp = $this->orderDateTime();

        if (!$timestamp instanceof \DateTime)
            $timestamp = new \DateTime($timestamp);

        if (Carbon::now()->subMinute()->gte($timestamp))
            return false;

        $orderType = $this->getOrderType($orderTypeCode);

        if (!$orderType->getFutureDays() && $this->isClosed())
            return false;

        $minFutureDays = Carbon::now()->startOfDay()->addDays($orderType->getMinimumFutureDays());
        $maxFutureDays = Carbon::now()->endOfDay()->addDays($orderType->getFutureDays());
        if (!$timestamp->between($minFutureDays, $maxFutureDays))
            return false;

        return $orderType->getSchedule()->isOpenAt($timestamp);
    }

    public function checkNoOrderTypeAvailable()
    {
        return $this->getOrderTypes()->filter(function ($orderType) {
            return !$orderType->isDisabled();
        })->isEmpty();
    }

    public function hasAsapSchedule()
    {
        if ($this->getOrderType()->getMinimumFutureDays())
            return false;

        return $this->getOrderType()->getScheduleRestriction() !== AbstractOrderType::LATER_ONLY;
    }

    public function hasLaterSchedule()
    {
        return $this->getOrderType()->getScheduleRestriction() !== AbstractOrderType::ASAP_ONLY;
    }

    //
    // DELIVERY AREA
    //

    public function getAreaId()
    {
        return $this->coveredArea()->getKey();
    }

    public function setCoveredArea(CoveredArea $coveredArea)
    {
        $this->coveredArea = $coveredArea;

        $areaId = $this->getSession('area');
        if ($areaId !== $coveredArea->getKey()) {
            $this->putSession('area', $coveredArea->getKey());
            $this->fireSystemEvent('location.area.updated', [$coveredArea]);
        }

        return $this;
    }

    public function isCurrentAreaId($areaId)
    {
        return $this->getAreaId() == $areaId;
    }

    public function clearCoveredArea()
    {
        $this->coveredArea = null;
        $this->forgetSession('area');
    }

    /**
     * @return \Igniter\Local\Classes\CoveredArea
     * @throws \Igniter\Flame\Exception\ApplicationException
     */
    public function coveredArea()
    {
        if (!is_null($this->coveredArea))
            return $this->coveredArea;

        $area = null;
        if ($areaId = (int)$this->getSession('area'))
            $area = $this->getModel()->findDeliveryArea($areaId);

        if ($area && $this->getId() !== $area->getLocationId()) {
            $area = null;
            $this->clearCoveredArea();
        }

        if (is_null($area)) {
            $area = $this->getModel()->searchOrDefaultDeliveryArea(
                $this->userPosition()->getCoordinates()
            );
        }

        if (!$area || !$area instanceof AreaInterface)
            return new CoveredArea(new Location_areas_model());

        $coveredArea = new CoveredArea($area);
        $this->setCoveredArea($coveredArea);

        return $coveredArea;
    }

    public function deliveryAreas()
    {
        return $this->getModel()->listDeliveryAreas();
    }

    public function deliveryAmount($cartTotal)
    {
        return $this->coveredArea()->deliveryAmount($cartTotal);
    }

    /**
     * @deprecated remove after v4, use minimumOrderTotal() instead
     */
    public function minimumOrder($cartTotal)
    {
        return $this->minimumOrderTotal();
    }

    public function getDeliveryChargeConditions()
    {
        return $this->coveredArea()->listConditions();
    }

    /**
     * @deprecated remove after v4, use checkMinimumOrderTotal() instead
     */
    public function checkMinimumOrder($cartTotal)
    {
        return $cartTotal >= $this->minimumOrderTotal();
    }

    public function checkDistance()
    {
        $distance = $this->getModel()->calculateDistance(
            $this->userPosition()->getCoordinates()
        );

        if (!$distance instanceof Distance)
            return $distance;

        return $distance->formatDistance($this->getModel()->getDistanceUnit());
    }

    public function checkDeliveryCoverage(UserLocation $userPosition = null)
    {
        if (is_null($userPosition))
            $userPosition = $this->userPosition();

        return $this->coveredArea()->checkBoundary($userPosition->getCoordinates());
    }
}
