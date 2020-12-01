<?php

namespace Igniter\Local\Components;

use Igniter\Local\Classes\CoveredArea;
use Igniter\Local\Classes\CoveredAreaCondition;
use Location;

class Info extends \System\Classes\BaseComponent
{
    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['infoTimeFormat'] = convert_php_to_moment_js_format(lang('system::lang.time_format'));
        $this->page['openingTimeFormat'] = convert_php_to_moment_js_format(lang('system::lang.date_time_format_long'));
        $this->page['lastOrderTimeFormat'] = convert_php_to_moment_js_format(lang('system::lang.date_time_format_long'));

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
        return $locationCurrent->listWorkingHours()->groupBy(function ($model) {
            return $model->day->isoFormat('dddd');
        });
    }

    protected function makeInfoObject()
    {
        $object = new \stdClass();

        $current = Location::current();

        $object->name = $current->getName();
        $object->description = $current->getDescription();

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
