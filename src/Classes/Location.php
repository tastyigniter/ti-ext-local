<?php

namespace Igniter\Local\Classes;

use Carbon\Carbon;
use Igniter\Cart\Classes\AbstractOrderType;
use Igniter\Flame\Geolite\Model\Distance;
use Igniter\Flame\Geolite\Model\Location as UserLocation;
use Igniter\Local\Contracts\AreaInterface;
use Igniter\Local\Models\Location as LocationModel;
use Igniter\Local\Models\LocationArea;
use Illuminate\Support\Collection;

/**
 * Location Class
 */
class Location extends Manager
{
    const CLOSED = 'closed';

    const OPEN = 'open';

    const OPENING = 'opening';

    protected ?CoveredArea $coveredArea = null;

    protected ?Collection $orderTypes = null;

    protected array $scheduleTimeslotCache = [];

    //
    // BOOT METHODS
    //

    public function updateNearbyArea(AreaInterface $area)
    {
        $this->setCurrent($area->location);

        $this->setCoveredArea(new CoveredArea($area));
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

    public function updateOrderType($code = null)
    {
        $oldOrderType = $this->getSession('orderType');

        if (is_null($code)) {
            $this->forgetSession('orderType');
        }

        if (strlen($code)) {
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

    //
    // HELPER METHODS
    //

    public function clearCoveredArea()
    {
        $this->coveredArea = null;
        $this->forgetSession('area');
    }

    public function updateScheduleTimeSlot($dateTime, $isAsap = true)
    {
        $orderType = $this->orderType();
        $oldSlot = $this->getSession($orderType.'-timeslot');

        $slot['dateTime'] = (!$isAsap && !is_null($dateTime)) ? make_carbon($dateTime) : null;
        $slot['isAsap'] = $isAsap;

        if (!$slot) {
            $this->forgetSession($orderType.'-timeslot');
        } else {
            $this->putSession($orderType.'-timeslot', $slot);
        }

        $this->fireSystemEvent('location.timeslot.updated', [$slot, $oldSlot]);
    }

    public function orderType()
    {
        return $this->getSession('orderType', LocationModel::DELIVERY);
    }

    public function requiresUserPosition()
    {
        return setting('location_order') == 1;
    }

    public function checkOrderType($code = null)
    {
        return !$this->getOrderType($code)->isDisabled();
    }

    /**
     * @return \Igniter\Cart\Classes\AbstractOrderType
     */
    public function getOrderType($code = null)
    {
        $code = !is_null($code) ? $code : $this->orderType();

        return $this->getOrderTypes()->get($code);
    }

    public function getOrderTypes()
    {
        if ($this->orderTypes) {
            return $this->orderTypes;
        }

        return $this->orderTypes = $this->getModel()?->availableOrderTypes();
    }

    public function orderTypeIsDelivery()
    {
        return $this->orderType() === LocationModel::DELIVERY;
    }

    public function orderTypeIsCollection()
    {
        return $this->orderType() === LocationModel::COLLECTION;
    }

    public function hasOrderType($code = null)
    {
        if (!$orderType = $this->getOrderType($code)) {
            return false;
        }

        return !$orderType->isDisabled();
    }

    public function getActiveOrderTypes()
    {
        return collect($this->getOrderTypes() ?? [])->filter(fn($orderType) => !$orderType->isDisabled());
    }

    public function openingSchedule()
    {
        return $this->workingSchedule(Location::OPENING);
    }

    //
    // HOURS
    //

    public function deliverySchedule()
    {
        return $this->workingSchedule(LocationModel::DELIVERY);
    }

    public function collectionSchedule()
    {
        return $this->workingSchedule(LocationModel::COLLECTION);
    }

    public function openTime($type = null, $format = null)
    {
        if (is_null($type)) {
            $type = $this->orderType();
        }

        return $this->workingSchedule($type)->getOpenTime($format);
    }

    public function closeTime($type = null, $format = null)
    {
        if (is_null($type)) {
            $type = $this->orderType();
        }

        return $this->workingSchedule($type)->getCloseTime($format);
    }

    public function lastOrderTime()
    {
        return Carbon::parse($this->getOrderType()->getSchedule()->getCloseTime());
    }

    public function checkOrderTime($timestamp = null, $orderTypeCode = null)
    {
        if (is_null($timestamp)) {
            $timestamp = $this->orderDateTime();
        }

        if (!$timestamp instanceof \DateTime) {
            $timestamp = new \DateTime($timestamp);
        }

        if (Carbon::now()->subMinute()->gte($timestamp)) {
            return false;
        }

        $orderType = $this->getOrderType($orderTypeCode);

        if (!$orderType->getFutureDays() && $this->isClosed()) {
            return false;
        }

        $minFutureDays = Carbon::now()->startOfDay()->addDays($orderType->getMinimumFutureDays());
        $maxFutureDays = Carbon::now()->endOfDay()->addDays($orderType->getFutureDays());
        if (!$timestamp->between($minFutureDays, $maxFutureDays)) {
            return false;
        }

        return $orderType->getSchedule()->isOpenAt($timestamp);
    }

    /**
     * @return \Carbon\Carbon
     */
    public function orderDateTime()
    {
        $dateTime = $this->getSession($this->orderType().'-timeslot.dateTime');
        if ($this->orderTimeIsAsap()) {
            $dateTime = $this->asapScheduleTimeslot();
        }

        if (!$dateTime || now()->isAfter($dateTime)) {
            $dateTime = $this->hasAsapSchedule()
                ? $this->asapScheduleTimeslot()
                : $this->firstScheduleTimeslot();
        }

        return make_carbon($dateTime);
    }

    public function orderTimeIsAsap()
    {
        if (!$this->hasAsapSchedule()) {
            return false;
        }

        $orderType = $this->orderType();
        $dateTime = $this->getSession($orderType.'-timeslot.dateTime');
        $orderTimeIsAsap = (bool)$this->getSession($orderType.'-timeslot.isAsap', true);

        if (!$this->isOpened()) {
            return false;
        }

        return $orderTimeIsAsap || ($dateTime && now()->isAfter($dateTime));
    }

    //
    // Timeslot
    //

    public function hasAsapSchedule()
    {
        if ($this->getOrderType()->getMinimumFutureDays()) {
            return false;
        }

        return $this->getOrderType()->getScheduleRestriction() !== AbstractOrderType::LATER_ONLY;
    }

    public function isOpened()
    {
        return $this->getOrderType()->getSchedule()->isOpen();
    }

    public function asapScheduleTimeslot()
    {
        if ($this->isClosed() || (bool)$this->getModel()->getSettings('checkout.limit_orders')) {
            return $this->firstScheduleTimeslot();
        }

        return Carbon::now();
    }

    public function isClosed()
    {
        return $this->getOrderType()->getSchedule()->isClosed();
    }

    public function firstScheduleTimeslot()
    {
        return $this->scheduleTimeslot()->collapse()->first();
    }

    public function scheduleTimeslot($orderType = null)
    {
        if (is_null($orderType)) {
            $orderType = $this->orderType();
        }

        if (array_key_exists($orderType, $this->scheduleTimeslotCache)) {
            return $this->scheduleTimeslotCache[$orderType];
        }

        $leadMinutes = $this->model->shouldAddLeadTime($orderType)
            ? $this->orderLeadTime() : 0;

        $result = $this->getOrderType($orderType)->getSchedule()->getTimeslot(
            $this->orderTimeInterval(), null, $leadMinutes
        );

        return $this->scheduleTimeslotCache[$orderType] = $result;
    }

    public function orderLeadTime()
    {
        return $this->getOrderType()->getLeadTime();
    }

    public function orderTimeInterval()
    {
        return $this->getOrderType()->getInterval();
    }

    public function checkNoOrderTypeAvailable()
    {
        return $this->getOrderTypes()->filter(function($orderType) {
            return !$orderType->isDisabled();
        })->isEmpty();
    }

    public function hasLaterSchedule()
    {
        return $this->getOrderType()->getScheduleRestriction() !== AbstractOrderType::ASAP_ONLY;
    }

    public function isCurrentAreaId($areaId)
    {
        return $this->getAreaId() == $areaId;
    }

    public function getAreaId()
    {
        return $this->coveredArea()->getKey();
    }

    //
    // DELIVERY AREA
    //

    /**
     * @return \Igniter\Local\Classes\CoveredArea
     * @throws \Igniter\Flame\Exception\ApplicationException
     */
    public function coveredArea()
    {
        if (!is_null($this->coveredArea)) {
            return $this->coveredArea;
        }

        $area = null;
        if ($areaId = (int)$this->getSession('area')) {
            $area = $this->getModel()->findDeliveryArea($areaId);
        }

        if ($area && $this->getId() !== $area->getLocationId()) {
            $area = null;
            $this->clearCoveredArea();
        }

        if (is_null($area)) {
            $area = $this->getModel()->searchOrDefaultDeliveryArea(
                $this->userPosition()->getCoordinates()
            );
        }

        if (!$area || !$area instanceof AreaInterface) {
            return new CoveredArea(new LocationArea());
        }

        $coveredArea = new CoveredArea($area);
        $this->setCoveredArea($coveredArea);

        return $coveredArea;
    }

    /**
     * @return \Igniter\Flame\Geolite\Model\Location
     */
    public function userPosition()
    {
        return $this->getSession('position', UserLocation::createFromArray([]));
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

    public function minimumOrderTotal($orderType = null)
    {
        return $this->getOrderType($orderType)->getMinimumOrderTotal();
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
        return $this->checkMinimumOrderTotal($cartTotal);
    }

    public function checkMinimumOrderTotal($cartTotal, $orderType = null)
    {
        return $cartTotal >= $this->minimumOrderTotal($orderType);
    }

    public function checkDistance()
    {
        $distance = $this->getModel()->calculateDistance(
            $this->userPosition()->getCoordinates()
        );

        if (!$distance instanceof Distance) {
            return $distance;
        }

        return $distance->formatDistance($this->getModel()->getDistanceUnit());
    }

    public function checkDeliveryCoverage(?UserLocation $userPosition = null)
    {
        if (is_null($userPosition)) {
            $userPosition = $this->userPosition();
        }

        return $this->coveredArea()->checkBoundary($userPosition->getCoordinates());
    }

    protected function workingStatus($type = null, $timestamp = null)
    {
        if (is_null($type)) {
            $type = $this->orderType();
        }

        return $this->workingSchedule($type)->checkStatus($timestamp);
    }
}
