<?php

namespace Igniter\Local\Components;

use Igniter\Local\Classes\CoveredArea;
use Igniter\Local\Classes\CoveredAreaCondition;
use Igniter\Local\Facades\Location;

class Info extends \System\Classes\BaseComponent
{
    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['infoTimeFormat'] = lang('system::lang.moment.time_format');
        $this->page['openingTimeFormat'] = lang('system::lang.moment.day_time_format_short');
        $this->page['lastOrderTimeFormat'] = lang('system::lang.moment.day_time_format');

        $this->page['locationInfo'] = $this->makeInfoObject();
    }

    public function getAreaConditionLabels(CoveredArea $area)
    {
        return $area->listConditions()->map(function (CoveredAreaCondition $condition) {
            return ucfirst(strtolower($condition->getLabel()));
        })->all();
    }

    protected function mapIntoCoveredArea($locationCurrent)
    {
        return $locationCurrent->listDeliveryAreas()->mapInto(CoveredArea::class);
    }

    protected function listWorkingHours($locationCurrent)
    {
        return $locationCurrent->getWorkingHours()->groupBy(function ($model) {
            return $model->day->isoFormat('dddd');
        });
    }

    protected function makeInfoObject()
    {
        $object = new \stdClass();

        $current = Location::current();

        $object->name = $current->getName();
        $object->description = $current->getDescription();

        $object->orderTypes = Location::getOrderTypes();

        $object->opensAllDay = $current->workingHourType('opening') == '24_7';
        $object->hasDelivery = $current->hasDelivery();
        $object->hasCollection = $current->hasCollection();
        $object->deliveryMinutes = $current->deliveryMinutes();
        $object->collectionMinutes = $current->collectionMinutes();
        $object->openingSchedule = Location::openingSchedule();
        $object->deliverySchedule = Location::deliverySchedule();
        $object->collectionSchedule = Location::collectionSchedule();
        $object->lastOrderTime = Location::lastOrderTime();

        $object->payments = $current->listAvailablePayments()->pluck('name')->all();
        $object->schedules = $this->listWorkingHours($current);
        $object->deliveryAreas = $this->mapIntoCoveredArea($current);

        $object->model = $current;

        return $object;
    }
}
