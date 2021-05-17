<?php

namespace Igniter\Local\Classes;

use Admin\Models\Location_areas_model;
use Admin\Models\Locations_model;
use Carbon\Carbon;
use Igniter\Flame\Geolite\Model\Location as UserLocation;
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

    protected $locationModel = 'Admin\Models\Locations_model';

    /**
     * @var \Igniter\Local\Classes\CoveredArea
     */
    protected $coveredArea;

    protected $scheduleCache = [];

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

    public function updateOrderType($orderType = null)
    {
        $oldOrderType = $this->getSession('orderType');

        if (is_null($orderType))
            $this->forgetSession('orderType');

        if (strlen($orderType)) {
            $this->putSession('orderType', $orderType);
            $this->fireSystemEvent('location.orderType.updated', [$orderType, $oldOrderType]);
        }
    }

    public function updateUserPosition(UserLocation $position)
    {
        $oldPosition = $this->getSession('position');

        $this->putSession('position', $position);

        $this->clearCoveredArea();

        $this->fireSystemEvent('location.position.updated', [$position, $oldPosition]);
    }

    public function updateScheduleTimeSlot($dateTime, $isAsap = TRUE)
    {
        $oldSlot = $this->getSession('order-timeslot');

        $slot['dateTime'] = !$isAsap ? make_carbon($dateTime) : null;
        $slot['isAsap'] = $isAsap;

        if (!$slot) {
            $this->forgetSession('order-timeslot');
        }
        else {
            $this->putSession('order-timeslot', $slot);
        }

        $this->fireSystemEvent('location.timeslot.updated', [$slot, $oldSlot]);
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

    public function checkOrderType($orderType = null)
    {
        $orderType = !is_null($orderType) ? $orderType : $this->orderType();

        $model = $this->getModel();
        $method = 'has'.ucfirst($orderType);

        return $model->methodExists($method) AND $model->$method();
    }

    public function orderTypeIsDelivery()
    {
        return $this->orderType() == Locations_model::DELIVERY;
    }

    public function orderTypeIsCollection()
    {
        return $this->orderType() == Locations_model::COLLECTION;
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
        return $this->workingSchedule($this->orderType())->isOpen();
    }

    public function isClosed()
    {
        return $this->workingSchedule($this->orderType())->isClosed();
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
        return $this->getModel()->getOrderTimeInterval($this->orderType());
    }

    public function lastOrderTime()
    {
        return Carbon::parse($this->closeTime($this->orderType()));
    }

    public function orderLeadTime()
    {
        return $this->getModel()->getOrderLeadTime($this->orderType());
    }

    public function orderTimeIsAsap()
    {
        if ((bool)$this->getModel()->getOption('limit_orders'))
            return FALSE;

        $dateTime = $this->getSession('order-timeslot.dateTime');
        $orderTimeIsAsap = (bool)$this->getSession('order-timeslot.isAsap', TRUE);

        return ($this->isOpened() AND $orderTimeIsAsap)
            OR ($dateTime AND now()->isAfter($dateTime));
    }

    /**
     * @return \Carbon\Carbon
     */
    public function orderDateTime()
    {
        $dateTime = $this->getSession('order-timeslot.dateTime');
        if ($this->orderTimeIsAsap() OR !$dateTime)
            $dateTime = $this->asapScheduleTimeslot();

        return make_carbon($dateTime);
    }

    public function scheduleTimeslot()
    {
        $orderType = $this->orderType();
        if (array_key_exists($orderType, $this->scheduleCache))
            return $this->scheduleCache[$orderType];

        $result = $this->workingSchedule($orderType)->getTimeslot(
            $this->orderTimeInterval(), null, $this->orderLeadTime()
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

    public function checkOrderTime($timestamp = null, $orderType = null)
    {
        if (is_null($timestamp))
            $timestamp = $this->orderDateTime();

        if (is_null($orderType))
            $orderType = $this->orderType();

        if (!$timestamp instanceof \DateTime)
            $timestamp = new \DateTime($timestamp);

        if (Carbon::now()->subMinute()->gte($timestamp))
            return FALSE;

        $days = $this->getModel()->hasFutureOrder($orderType)
            ? $this->getModel()->futureOrderDays($orderType) : 0;

        if ($days < Carbon::now()->diffInDays($timestamp))
            return FALSE;

        return $this->workingSchedule($orderType)->isOpenAt($timestamp);
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
     * @throws \ApplicationException
     */
    public function coveredArea()
    {
        if (!is_null($this->coveredArea))
            return $this->coveredArea;

        $area = null;
        if ($areaId = (int)$this->getSession('area'))
            $area = $this->getModel()->findDeliveryArea($areaId);

        if ($area AND $this->getId() !== $area->getLocationId()) {
            $area = null;
            $this->clearCoveredArea();
        }

        if (is_null($area)) {
            $area = $this->getModel()->searchOrDefaultDeliveryArea(
                $this->userPosition()->getCoordinates()
            );
        }

        if (!$area OR !$area instanceof AreaInterface)
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

    public function minimumOrder($cartTotal)
    {
        return $this->coveredArea()->minimumOrderTotal($cartTotal);
    }

    public function getDeliveryChargeConditions()
    {
        return $this->coveredArea()->listConditions();
    }

    public function checkMinimumOrder($cartTotal)
    {
        return $cartTotal >= $this->minimumOrder($cartTotal);
    }

    public function checkDistance($decimalPoint)
    {
        $coordinates = $this->userPosition()->getCoordinates();
        $distance = $this->getModel()->calculateDistance($coordinates);

        return round($distance, $decimalPoint);
    }

    public function checkDeliveryCoverage(UserLocation $userPosition = null)
    {
        if (is_null($userPosition))
            $userPosition = $this->userPosition();

        return $this->coveredArea()->checkBoundary($userPosition->getCoordinates());
    }
}
