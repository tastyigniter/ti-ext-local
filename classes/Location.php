<?php namespace SamPoyigi\Local\Classes;

use Carbon\Carbon;
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

    public function searchNearby(GeoPosition $position, $update = TRUE)
    {
        $coordinates = [
            'latitude'  => $position->latitude,
            'longitude' => $position->longitude,
        ];

        $locationModel = $this->searchByCoordinates($coordinates);

        if (!is_null($locationModel)) {
            $this->updateUserPosition($position);

            if ($update)
                $this->setCurrent($locationModel);

            return TRUE;
        }

        return FALSE;
    }

    public function updateOrderType($orderType = null)
    {
        if (strlen($orderType)) {
            $this->putSession('orderType', $orderType);
        }
        else {
            $this->forgetSession('orderType');
        }
    }

    public function updateUserPosition(GeoPosition $position)
    {
        $this->putSession('position', $position);

        $this->fireEvent('position.updated', $position);
    }

    //
    //	HELPER METHODS
    //

    public function getId()
    {
        return $this->getModel()->getKey();
    }

    public function lastOrderTime($timeFormat = null)
    {
        $lastOrderMinutes = $this->getModel()->lastOrderMinutes();
        $closeTime = $this->closeTime($this->orderType(), $timeFormat);

        return (is_numeric($lastOrderMinutes) AND $lastOrderMinutes > 0)
            ? $closeTime->subMinutes($lastOrderMinutes)
            : $closeTime;
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

    //
    //	HOURS
    //

    public function openingSchedule()
    {
        return $this->getModel()->workingSchedule(static::OPENING);
    }

    public function deliverySchedule()
    {
        return $this->getModel()->workingSchedule('delivery');
    }

    public function collectionSchedule()
    {
        return $this->getModel()->workingSchedule('collection');
    }

    public function workingSchedule($type)
    {
        return $this->getModel()->workingSchedule($type);
    }

    public function isOpened()
    {
        return $this->openingSchedule()->isOpen();
    }

    public function isClosed()
    {
        return $this->openingSchedule()->isClosed();
    }

    public function openTime($type = null, $format = null)
    {
        if (is_null($type))
            $type = static::OPENING;

        if (is_null($format))
            $format = setting('time_format');

        return $this->workingSchedule($type)->getOpenTime($format);
    }

    public function closeTime($type = null, $format = null)
    {
        if (is_null($type))
            $type = static::OPENING;

        if (is_null($format))
            $format = setting('time_format');

        return $this->workingSchedule($type)->getCloseTime($format);
    }

    public function workingStatus($type = null, $timestamp = null)
    {
        if (is_null($type))
            $type = static::OPENING;

        return $this->workingSchedule($type)->getStatus($timestamp);
    }

    public function orderTimeInterval()
    {
        return $this->getModel()->getOrderTimeInterval($this->orderType());
    }

    public function orderTimePeriods()
    {
        $dateTime = Carbon::now();
        $orderType = $this->orderType();

        $schedule = $this->workingSchedule($orderType);

        if ($schedule->isClosed() OR !$this->checkOrderType()) return null;

        $daysInAdvance = $this->getModel()->hasFutureOrder()
            ? $this->getModel()->futureOrderDays($orderType) : 1;

        $schedule->setDaysInAdvance($daysInAdvance);

        return $schedule->generatePeriods($dateTime);
    }

    public function orderTimeRange()
    {
        $dateTime = Carbon::now();
        $orderType = $this->orderType();

        $schedule = $this->workingSchedule($orderType);

        if ($this->isClosed() OR !$this->checkOrderType()) return null;

        $timeInterval = $this->orderTimeInterval();
        $daysInAdvance = $this->getModel()->hasFutureOrder()
            ? $this->getModel()->futureOrderDays($orderType) : 1;

        $schedule->setDaysInAdvance($daysInAdvance);

        return $schedule->generatePeriodsWithTimes($dateTime, $timeInterval);
    }

    public function checkOrderTime($timestamp, $orderType = null)
    {
        $status = $this->workingSchedule($orderType)->getStatus($timestamp);

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

        if ($locationId !== $this->getId())
            return null;

        return $areaId;
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
        return $this->getModel()->findDeliveryArea($this->getAreaId());
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