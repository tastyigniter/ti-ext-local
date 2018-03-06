<?php

namespace SamPoyigi\Local\Components;

use Location;

class Info extends \System\Classes\BaseComponent
{
    public $isHidden = TRUE;

    protected $list = [];

    public function onRun()
    {
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $currentLocation = Location::current();

        $this->id = uniqid($this->alias);
        $this->page['currentLocation'] = $currentLocation;
        $this->page['hasDelivery'] = $currentLocation->hasDelivery();
        $this->page['hasCollection'] = $currentLocation->hasCollection();
        $this->page['deliveryTime'] = $currentLocation->deliveryMinutes();
        $this->page['collectionTime'] = $currentLocation->collectionMinutes();
//        $this->page['deliveryHour'] = Location::openTime('delivery');
//        $this->page['collectionHour'] = Location::openTime('collection');

        $this->page['localPayments'] = $currentLocation->listAvailablePayments();
        $this->page['localHours'] = $currentLocation->listWorkingHours()->groupBy('day');
        $this->page['deliveryAreas'] = $currentLocation->listDeliveryAreas();

        $this->page['openingType'] = $currentLocation->workingHourType('opening');
        $this->page['workingTypes'] = $currentLocation->availableWorkingTypes();
        $this->page['deliveryStatus'] = Location::workingStatus('delivery');
        $this->page['collectionStatus'] = Location::workingStatus('collection');
        $this->page['lastOrderTime'] = Location::lastOrderTime();

        $userPosition = Location::userPosition();

        $this->page['locationLat'] = $userPosition->latitude;
        $this->page['locationLng'] = $userPosition->longitude;
        $this->page['mapAddress'] = format_address($currentLocation->getAddress());
        $this->page['locationTelephone'] = $currentLocation->getTelephone();
        $this->page['locationDescription'] = $currentLocation->getDescription();
    }
}