<?php namespace SamPoyigi\Local\Classes;

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

    public function setOrderType($orderType = null)
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
        $this->putSession('position', [
            'latitude'         => $position->latitude,
            'longitude'        => $position->longitude,
            'formattedAddress' => $position->formattedAddress,
        ]);

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

    public function getDefaultLocal()
    {
        return $this->getById($this->getDefaultLocation());
    }

    public function userPosition()
    {
        if (!$this->userPosition)
            $this->userPosition = new GeoPosition();

        $this->userPosition->fillFromArray($this->getSession('position', []));

        return $this->userPosition;
    }

    public function requiresUserPosition()
    {
        return setting('location_order') == 1;
    }

    public function orderType()
    {
        return $this->getSession('orderType', 'delivery');
    }

    public function checkOrderType($orderType = null)
    {
        $orderType = !is_null($orderType) ? $orderType : $this->orderType();

        $workingStatus = $this->workingStatus($orderType);

        $isOpen = !($workingStatus == static::CLOSED OR !$this->getModel()->{'has'.ucfirst($orderType)}());
        $isOpening = (!$this->getModel()->hasFutureOrder() AND $workingStatus == static::OPENING);

        return ($isOpen OR $isOpening);
    }

    //
    //	HOURS
    //

    public function isOpened()
    {
        return $this->workingSchedule('opening')->isOpen();
    }

    public function isClosed()
    {
        return $this->workingSchedule('opening')->isClosed();
    }

    public function openTime($type = 'opening', $format = null)
    {
        if (is_null($format))
            $format = setting('time_format');

        return $this->workingSchedule($type)->getOpenTime($format);
    }

    public function closeTime($type = 'opening', $format = null)
    {
        if (is_null($format))
            $format = setting('time_format');

        return $this->workingSchedule($type)->getCloseTime($format);
    }

    public function workingStatus($type = 'opening', $timestamp = null)
    {
        return $this->workingSchedule($type)->getStatus($timestamp);
    }

    public function orderTimeInterval()
    {
        return $this->getModel()->getOrderTimeInterval($this->orderType());
    }

    public function orderTimePeriods()
    {
        if ($this->isClosed() OR !$this->checkOrderType()) return null;

        $orderType = $this->orderType();

        $daysInAdvance = ($this->getModel()->hasFutureOrder()) ? $this->getModel()->futureOrderDays($orderType) : 1;

        return $this->workingSchedule($orderType)->generatePeriods($daysInAdvance);
    }

    public function orderTimeRange()
    {
        if ($this->isClosed() OR !$this->checkOrderType()) return null;

        $orderType = $this->orderType();
        $daysInAdvance = ($this->getModel()->hasFutureOrder()) ? $this->getModel()->futureOrderDays($orderType) : 1;
        $timeInterval = $this->orderTimeInterval();

        return $this->workingSchedule($orderType)->generatePeriodsWithTimes($daysInAdvance, $timeInterval);
    }

    public function checkOrderTime($timestamp, $orderType = null)
    {
        $status = $this->workingSchedule($orderType)->getStatus($timestamp);

        if ($this->getModel()->hasFutureOrder() AND $status != static::CLOSED)
            return TRUE;

        return ($status == static::OPEN);
    }

    protected function workingSchedule($type)
    {
        return $this->getModel()->workingSchedule($type);
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