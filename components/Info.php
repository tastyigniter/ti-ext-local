<?php

namespace Igniter\Local\Components;

use Location;

class Info extends \System\Classes\BaseComponent
{
    public $isHidden = TRUE;

    protected $list = [];

    public function onRun()
    {
        $this->page['location'] = Location::instance();
        $this->page['locationCurrent'] = Location::current();

        $locationCurrent = Location::current();
        $this->page['localPayments'] = $locationCurrent->listAvailablePayments();
        $this->page['localHours'] = $locationCurrent->listWorkingHours()->groupBy('day');
        $this->page['deliveryAreas'] = $locationCurrent->listDeliveryAreas();
    }
}