<?php namespace Igniter\Local\Traits;

use ApplicationException;
use Exception;
use Geocoder;
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
     * @throws \ApplicationException
     */
    protected function geocodeSearchQuery($searchQuery)
    {
        $collection = Geocoder::geocode($searchQuery);

        if (!$collection OR $collection->isEmpty())
            throw new ApplicationException(implode(PHP_EOL, Geocoder::getLogs()));

        $userLocation = $collection->first();
        if (!$userLocation->hasCoordinates())
            throw new ApplicationException(lang('igniter.local::default.alert_invalid_search_query'));

        Location::updateUserPosition($userLocation);

        return $userLocation;
    }
}