<?php

namespace Igniter\Local\Models\Concerns;

use Igniter\Flame\Geolite\Contracts\CoordinatesInterface;
use Igniter\Local\Models\LocationSettings;

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

    public function setUrl($suffix = null)
    {
        if (is_single_location()) {
            $suffix = '/menus';
        }

        $this->url = page_url($this->permalink_slug.$suffix);
    }

    public function hasGallery()
    {
        return $this->hasMedia('gallery');
    }

    public function getGallery()
    {
        return $this->getMedia('gallery');
    }

    public function getSettings(string $item, mixed $default = null): mixed
    {
        return array_get($this->grouped_settings, $item, $default);
    }

    public function findSettings(string $item): LocationSettings
    {
        return $this->settings()->firstOrNew(['item' => $item]);
    }

    public function getGroupedSettingsAttribute(): mixed
    {
        return $this->settings->mapWithKeys(function($setting) {
            return [$setting->item => $setting->data];
        })->all();
    }
}
