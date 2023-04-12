<?php

namespace Igniter\Local\Traits;

use Exception;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Flame\Geolite\Facades\Geocoder;
use Igniter\Local\Facades\Location;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

trait SearchesNearby
{
    public function getSearchQuery()
    {
        return post('search_query', Location::getSession('searchQuery'));
    }

    public function onSearchNearby()
    {
        try {
            if (!$searchQuery = $this->getRequestSearchQuery())
                throw new ApplicationException(lang('igniter.local::default.alert_no_search_query'));

            $userLocation = is_array($searchQuery)
                ? $this->geocodeSearchPoint($searchQuery)
                : $this->geocodeSearchQuery($searchQuery);

            $nearByLocation = Location::searchByCoordinates(
                $userLocation->getCoordinates()
            )->first(function ($location) use ($userLocation) {
                if ($area = $location->searchDeliveryArea($userLocation->getCoordinates())) {
                    Location::updateNearbyArea($area);

                    return $area;
                }
            });

            if (!$nearByLocation) {
                throw new ApplicationException(lang('igniter.local::default.alert_no_found_restaurant'));
            }

            if ($redirectPage = post('redirect'))
                return Redirect::to($this->controller->pageUrl($redirectPage));

            return Redirect::to(restaurant_url($this->property('menusPage'), ['location' => null]));
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->danger($ex->getMessage());
        }
    }

    protected function getRequestSearchQuery()
    {
        if ($coordinates = post('search_point'))
            return $coordinates;

        return post('search_query');
    }

    /**
     * @param $searchQuery
     * @return \Igniter\Flame\Geolite\Model\Location
     * @throws \Igniter\Flame\Exception\ApplicationException
     */
    protected function geocodeSearchQuery($searchQuery)
    {
        $collection = Geocoder::geocode($searchQuery);

        $userLocation = $this->handleGeocodeResponse($collection);

        Location::putSession('searchQuery', $searchQuery);

        return $userLocation;
    }

    protected function geocodeSearchPoint($searchPoint)
    {
        if (count($searchPoint) !== 2)
            throw new ApplicationException(lang('igniter.local::default.alert_no_search_query'));

        [$latitude, $longitude] = $searchPoint;
        if (!strlen($latitude) || !strlen($longitude))
            throw new ApplicationException(lang('igniter.local::default.alert_no_search_query'));

        $collection = Geocoder::reverse($latitude, $longitude);

        $userLocation = $this->handleGeocodeResponse($collection);

        Location::putSession('searchPoint', $searchPoint);
        Location::putSession('searchQuery', $userLocation->format());

        return $userLocation;
    }

    protected function handleGeocodeResponse($collection)
    {
        if (!$collection || $collection->isEmpty()) {
            Log::error(implode(PHP_EOL, Geocoder::getLogs()));
            throw new ApplicationException(lang('igniter.local::default.alert_invalid_search_query'));
        }

        $userLocation = $collection->first();
        if (!$userLocation->hasCoordinates())
            throw new ApplicationException(lang('igniter.local::default.alert_invalid_search_query'));

        Location::updateUserPosition($userLocation);

        return $userLocation;
    }
}
