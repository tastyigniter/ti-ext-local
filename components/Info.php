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
            ],
        ];
    }

    public function onRun()
    {
        $this->page['infoTimeFormat'] = $this->property('infoTimeFormat');

        $this->page['location'] = Location::instance();
        $this->page['locationCurrent'] = $locationCurrent = Location::current();

        $this->page['localPayments'] = $locationCurrent->listAvailablePayments();
        $this->page['localHours'] = $this->listWorkingHours($locationCurrent);
        $this->page['deliveryAreas'] = $this->mapIntoCoveredArea($locationCurrent);
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
}