<?php namespace Igniter\Local\Traits;

use ApplicationException;
use Exception;
use Igniter\Flame\Location\GeoPosition;
use Location;
use Redirect;
use Request;

trait SearchesNearby
{
    public function onSearchNearby()
    {
        try {
            if (!strlen($searchQuery = post('search_query')))
                throw new ApplicationException(lang('igniter.local::default.alert_no_search_query'));

            $position = $this->geocodeSearchQuery($searchQuery);

            $nearByLocations = Location::searchByCoordinates([
                'latitude' => $position->latitude,
                'longitude' => $position->longitude,
            ])->take(10);

            $nearByLocation = $nearByLocations->first(function ($location) use ($position) {
                if ($area = $location->filterDeliveryArea($position)) {
                    Location::updateNearby($position, $area);
                }

                return $area;
            });

            if (!$nearByLocation) {
                throw new ApplicationException(lang('igniter.local::default.alert_no_found_restaurant'));
            }

            if ($redirectPage = post('redirect'))
                return Redirect::to($this->controller->pageUrl($redirectPage));

            return Redirect::to(restaurant_url($this->property('menusPage')));
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->danger($ex->getMessage());
        }
    }

    protected function geocodeSearchQuery($searchQuery)
    {
        $userPosition = app('geocoder')->geocode([
            'address' => $searchQuery,
        ]);

        if (!$userPosition OR !$userPosition instanceof GeoPosition)
            throw new ApplicationException(lang('igniter.local::default.alert_unknown_error'));

        switch ($userPosition->status) {
            case 'ZERO_RESULTS':
            case 'INVALID_REQUEST':
            case 'UNKNOWN_ERROR':
                throw new ApplicationException($userPosition->errorMessage
                    ?? lang('igniter.local::default.alert_invalid_search_query'));
            case 'REQUEST_DENIED':
            case 'OVER_QUERY_LIMIT':
                throw new ApplicationException($userPosition->errorMessage
                    ?? lang('igniter.local::default.alert_unknown_error'));
        }

        return $userPosition;
    }
}