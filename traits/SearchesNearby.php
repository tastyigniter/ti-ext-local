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
            if (!strlen($searchQuery = post('search_query')))
                throw new ApplicationException(lang('igniter.local::default.alert_no_search_query'));

            $userLocation = $this->geocodeSearchQuery($searchQuery);

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

            return Redirect::to(restaurant_url($this->property('menusPage')));
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->danger($ex->getMessage());
        }
    }

    /**
     * @param $searchQuery
     * @return \Igniter\Flame\Geolite\Model\Location
     * @throws \Igniter\Flame\Exception\ApplicationException
     */
    protected function geocodeSearchQuery($searchQuery)
    {
        $collection = Geocoder::geocode($searchQuery);

        if (!$collection OR $collection->isEmpty()) {
            Log::error(implode(PHP_EOL, Geocoder::getLogs()));
            throw new ApplicationException(lang('igniter.local::default.alert_invalid_search_query'));
        }

        $userLocation = $collection->first();
        if (!$userLocation->hasCoordinates())
            throw new ApplicationException(lang('igniter.local::default.alert_invalid_search_query'));

        Location::updateUserPosition($userLocation);

        Location::putSession('searchQuery', $searchQuery);

        return $userLocation;
    }
}
