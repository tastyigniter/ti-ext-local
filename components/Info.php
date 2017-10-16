<?php

namespace SamPoyigi\Local\Components;

use Igniter\Libraries\Location\Location;

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
        if (!$library = $this->property('library'))
            throw new \Exception("Missing [location library] property in {$this->alias} component");

        $libraryModel = $library->getModel();

        $this->id = uniqid($this->alias);
        $this->page['localPayments'] = $library->payments();
        $this->page['hasDelivery'] = $libraryModel->offersOrderType('delivery');
        $this->page['hasCollection'] = $libraryModel->offersOrderType('collection');
        $this->page['deliveryTime'] = $library->deliveryTime();
        $this->page['collectionTime'] = $library->collectionTime();
        $this->page['deliveryHour'] = $library->workingTime('delivery');
        $this->page['collectionHour'] = $library->workingTime('collection');

        $this->page['localTimeFormat'] = $timeFormat = config_item('time_format');
        $this->page['localHours'] = $library->workingHours()->generateHours();
        $this->page['workingHourType'] = $library->workingType();
        $this->page['workingTypes'] = $library->workingHours()->getTypes();
        $this->page['deliveryStatus'] = $library->workingStatus('delivery');
        $this->page['collectionStatus'] = $library->workingStatus('collection');
        $this->page['lastOrderTime'] = mdate($timeFormat, strtotime($library->lastOrderTime()));

        $localPosition = $library->area()->localPosition();

        $this->page['deliveryAreas'] = $library->deliveryAreas();
        $this->page['locationLat'] = $localPosition->latitude;
        $this->page['locationLng'] = $localPosition->longitude;
        $this->page['mapAddress'] = $library->getAddress();
        $this->page['locationTelephone'] = $library->getTelephone();
    }
}