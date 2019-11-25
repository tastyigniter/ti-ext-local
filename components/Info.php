<?php

namespace Igniter\Local\Components;

use Igniter\Local\Classes\CoveredArea;
use Igniter\Local\Classes\CoveredAreaCondition;
use Location;

class Info extends \System\Classes\BaseComponent
{
    public $isHidden = TRUE;

    protected $list = [];

    public function onRun()
    {
        $this->page['location'] = Location::instance();
        $this->page['locationCurrent'] = $locationCurrent = Location::current();

        $this->page['localPayments'] = $locationCurrent->listAvailablePayments();
        $this->page['localHours'] = $locationCurrent->listWorkingHours()->groupBy('day');
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
}