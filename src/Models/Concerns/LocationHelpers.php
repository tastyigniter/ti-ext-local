<?php

namespace Igniter\Local\Models\Concerns;

use Igniter\Flame\Geolite\Contracts\CoordinatesInterface;
use Igniter\Local\Classes\OrderTypes;

trait LocationHelpers
{
    public function getName()
    {
        return $this->location_name;
    }

    public function getEmail()
    {
        return strtolower($this->location_email);
    }

    public function getTelephone()
    {
        return $this->location_telephone;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getAddress()
    {
        $country = optional($this->country);

        return [
            'address_1' => $this->location_address_1,
            'address_2' => $this->location_address_2,
            'city' => $this->location_city,
            'state' => $this->location_state,
            'postcode' => $this->location_postcode,
            'location_lat' => $this->location_lat,
            'location_lng' => $this->location_lng,
            'country_id' => $this->location_country_id,
            'country' => $country->country_name,
            'iso_code_2' => $country->iso_code_2,
            'iso_code_3' => $country->iso_code_3,
            'format' => $country->format,
        ];
    }

    public function availableOrderTypes()
    {
        return resolve(OrderTypes::class)->makeOrderTypes($this);
    }

    public static function getOrderTypeOptions()
    {
        return collect(resolve(OrderTypes::class)->listOrderTypes())->pluck('name', 'code');
    }

    public function calculateDistance(CoordinatesInterface $position)
    {
        $distance = $this->makeDistance();

        $distance->setFrom($this->getCoordinates());
        $distance->setTo($position);
        $distance->in($this->getDistanceUnit());

        return app('geocoder')->distance($distance);
    }

    /**
     * @return \Igniter\Flame\Geolite\Model\Coordinates
     */
    public function getCoordinates()
    {
        return app('geolite')->coordinates($this->location_lat, $this->location_lng);
    }

    /**
     * @return \Igniter\Flame\Geolite\Contracts\DistanceInterface
     */
    public function makeDistance()
    {
        return app('geolite')->distance();
    }
}
