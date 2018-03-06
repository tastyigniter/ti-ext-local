<?php namespace SamPoyigi\Local\Traits;

use AjaxException;
use Exception;
use Igniter\Flame\Location\GeoPosition;
use Location;
use ApplicationException;
use Redirect;
use Request;

trait SearchesNearby
{
    public function onSearchNearby()
    {
        try {
            if (!strlen($searchQuery = post('search_query')))
                throw new ApplicationException(lang('sampoyigi.local::default.alert_no_search_query'));

            $position = $this->geocodeSearch($searchQuery);

            if (!Location::searchNearby($position)) {
                throw new ApplicationException(lang('sampoyigi.local::default.alert_no_found_restaurant'));    // display error: no available restaurant
            }

            return Redirect::to(restaurant_url($this->property('menusPage')));
        } catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->danger($ex->getMessage());
        }
    }

    protected function geocodeSearch($searchQuery)
    {
        $userPosition = app('geocoder')->geocode([
            'address' => $searchQuery
        ]);

        if (!$userPosition OR !$userPosition instanceof GeoPosition)
            throw new ApplicationException(lang('sampoyigi.local::default.alert_unknown_error'));

        switch ($userPosition->status) {
            case 'ZERO_RESULTS':
            case 'INVALID_REQUEST':
            case 'UNKNOWN_ERROR':
                throw new ApplicationException($userPosition->errorMessage
                    ? $userPosition->errorMessage
                    : lang('sampoyigi.local::default.alert_invalid_search_query'));
            case 'REQUEST_DENIED':
            case 'OVER_QUERY_LIMIT':
                throw new ApplicationException($userPosition->errorMessage
                    ? $userPosition->errorMessage
                    : lang('sampoyigi.local::default.alert_unknown_error'));
        }

        return $userPosition;
    }
}