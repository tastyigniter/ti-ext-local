<?php

namespace Igniter\Local\Components;

use Igniter\Local\Classes\CoveredArea;
use Igniter\Local\Classes\CoveredAreaCondition;
use Location;

class Info extends \System\Classes\BaseComponent
{
    public function defineProperties()
    {
        return [
            'infoTimeFormat' => [
                'label' => 'Date format for the open and close hours',
                'type' => 'text',
                'default' => 'HH:mm',
                'validationRule' => 'required|string',
            ],
            'openingTimeFormat' => [
                'label' => 'Time format for the opening later time',
                'type' => 'text',
                'span' => 'left',
                'default' => 'ddd hh:mm a',
                'validationRule' => 'required|string',
            ],
            'lastOrderTimeFormat' => [
                'label' => 'Date format for the last order time',
                'type' => 'text',
                'default' => 'ddd DD HH:mm',
                'validationRule' => 'required|string',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['infoTimeFormat'] = $this->property('infoTimeFormat');
        $this->page['openingTimeFormat'] = $this->property('openingTimeFormat');
        $this->page['lastOrderTimeFormat'] = $this->property('lastOrderTimeFormat');

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

        $object->model = $location;

        return $object;
    }
}
