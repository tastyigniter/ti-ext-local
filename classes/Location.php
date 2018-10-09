<?php namespace Igniter\Local\Classes;

use Admin\Models\Locations_model;
use Igniter\Flame\Location\GeoPosition;
use Igniter\Flame\Location\Manager;
use Igniter\Flame\Location\Models\Area;

/**
 * Location Class
 * @package System
 */
class Location extends Manager
{
    const CLOSED = 'closed';

    const OPEN = 'open';

    const OPENING = 'opening';

    protected $locationModel = 'Admin\Models\Locations_model';

    /**
     * @var GeoPosition
     */
    protected $userPosition;

    //
    //	BOOT METHODS
    //

    public function updateNearby(GeoPosition $position, $area)
    {
        $this->updateUserPosition($position);

        $this->setCurrent($area->location);

        $this->setCoveredArea($area);
    }

    public function updateOrderType($orderType = null)
    {
        if (is_null($orderType))
            $this->forgetSession('orderType');

        if (strlen($orderType)) {
            $this->putSession('orderType', $orderType);
        }
    }

    public function updateUserPosition(GeoPosition $position)
    {
        $this->putSession('position', $position);

        $this->fireEvent('position.updated', $position);
    }

    public function updateScheduleTimeSlot($dateTime = null, $type = null)
    {
        $slot = [];
        if (!is_null($dateTime))
            $slot['dateTime'] = make_carbon($dateTime, FALSE);

        if (!is_null($type))
            $slot['type'] = $type;

        if (!$slot) {
            $this->forgetSession('order.timeslot');
        }
        else {
            $this->putSession('order.timeslot', $slot);
        }
    }

    //
    //	HELPER METHODS
    //

    public function getId()
    {
        return $this->getModel()->getKey();
    }

    public function orderType()
    {
        return $this->getSession('orderType', 'delivery');
    }

    public function userPosition()
    {
        return $this->getSession('position', new GeoPosition);
    }

    public function requiresUserPosition()
    {
        return setting('location_order') == 1;
    }

    public function checkOrderType($orderType = null)
    {
        $orderType = !is_null($orderType) ? $orderType : $this->orderType();

        $workingStatus = $this->workingStatus($orderType);
        $model = $this->getModel();
        $method = 'has'.ucfirst($orderType);

        $isOpen = !(
            $workingStatus == static::CLOSED
            OR ($model->methodExists($method) AND !$model->$method())
        );

        $isOpening = (
            !$this->getModel()->hasFutureOrder()
            AND $workingStatus == static::OPENING
        );

        return ($isOpen OR $isOpening);
    }

    public function orderTypeIsDelivery()
    {
        return $this->orderType() == Locations_model::DELIVERY;
    }

    //
    //	HOURS
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

        if (is_null($format))
            $format = setting('time_format');

        return $this->workingSchedule($type)->getOpenTime($format);
    }

    public function closeTime($type = null, $format = null)
    {
        if (is_null($type))
            $type = $this->orderType();

        if (is_null($format))
            $format = setting('time_format');

        return $this->workingSchedule($type)->getCloseTime($format);
    }

    public function workingStatus($type = null, $timestamp = null)
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

    public function lastOrderTime($timeFormat = null)
    {
        $lastOrderMinutes = $this->getModel()->lastOrderMinutes();
        $closeTime = $this->closeTime($this->orderType(), $timeFormat);

        return (is_numeric($lastOrderMinutes) AND $lastOrderMinutes > 0)
            ? $closeTime->subMinutes($lastOrderMinutes)
            : $closeTime;
    }

    public function orderTimeIsAsap()
    {
        return array_get($this->getSession('order.timeslot'), 'type', 1);
    }

    /**
     * @return \Carbon\Carbon
     */
    public function orderDateTime()
    {
        $dateTime = $this->scheduleTimeslot()->first();
        if (!$this->orderTimeIsAsap())
            $dateTime = array_get($this->getSession('order.timeslot'), 'dateTime', $dateTime);

        return make_carbon($dateTime)->copy();
    }

    public function scheduleTimeslot()
    {
        return $this->workingSchedule($this->orderType())->getTimeslot();
    }

    public function checkOrderTime($timestamp, $orderType = null)
    {
        if (is_null($orderType))
            $orderType = $this->orderType();

        $status = $this->workingSchedule($orderType)->checkStatus($timestamp);
        if ($this->getModel()->hasFutureOrder() AND $status != static::CLOSED)
            return TRUE;

        return ($status == static::OPEN);
    }

    //
    //	DELIVERY AREA
    //

    public function getAreaId()
    {
        list ($areaId, $locationId) = $this->getSession('area', [null, null]);

        if ($areaId AND $locationId == $this->getId())
            return $areaId;

        return null;
    }

    public function setCoveredArea(Area $areaModel)
    {
        if ($this->getId() != $areaModel->getLocationId()) {
            $this->clearCoveredArea();
        }
        else {
            $areaId = $areaModel->getKey();
            $locationId = $areaModel->getLocationId();

            $this->putSession('area', [$areaId, $locationId]);
        }

        return $this;
    }

    public function isCurrentAreaId($areaId)
    {
        return $this->getAreaId() == $areaId;
    }

    public function clearCoveredArea()
    {
        $this->forgetSession('area');
    }

    /**
     * @return \Igniter\Flame\Location\Models\Area
     */
    public function coveredArea()
    {
        return $this->getModel()->findOrNewDeliveryArea($this->getAreaId());
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
        return ($cartTotal >= $this->minimumOrder($cartTotal));
    }

    public function checkDistance($decimalPoint)
    {
        $distance = $this->getModel()->calculateDistance($this->userPosition());

        return round($distance, $decimalPoint);
    }

    public function checkDeliveryCoverage(GeoPosition $userPosition = null)
    {
        if (is_null($userPosition))
            $userPosition = $this->userPosition();

        if (!$area = $this->coveredArea())
            return null;

        return $area->checkBoundary($userPosition);
    }
}