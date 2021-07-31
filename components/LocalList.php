<?php

namespace Igniter\Local\Components;

use Admin\Models\Addresses_model;
use Admin\Models\Locations_model;
use ErrorException;
use Exception;
use Igniter\Flame\Geolite\Facades\Geocoder;
use Illuminate\Support\Facades\Redirect;
use Igniter\Local\Facades\Location;
use Igniter\Local\Traits\SearchesNearby;

class LocalList extends \System\Classes\BaseComponent
{
    use SearchesNearby;

    public function defineProperties()
    {
        return [
            'distanceUnit' => [
                'label' => 'Distance unit to use, mi or km',
                'type' => 'text',
                'default' => 'mi',
                'validationRule' => 'required|in:km,mi',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['distanceUnit'] = $this->property('distanceUnit', setting('distance_unit'));
        $this->page['openingTimeFormat'] = lang('system::lang.moment.day_time_format_short');
        $this->page['filterSearch'] = input('search', $this->getSearchQuery());
        $this->page['filterSorted'] = input('sort_by');
        $this->page['filterSorters'] = $this->loadFilters();

        $this->page['userPosition'] = Location::userPosition();

        $this->page['locationsList'] = $this->loadList();

        if ($updatedLocation = $this->updateLocation()) {
            return Redirect::back();
        }
    }

    protected function updateLocation()
    {
        $lat = $this->param('lat');
        $lng = $this->param('lng');

        if ($lat!="" and $lng!="") {
            $collection = Geocoder::reverse($lat, $lng);

            if ($collection AND !$collection->isEmpty()) {
                $userLocation  = $collection->first();

                if ($userLocation->hasCoordinates()) {
                    Location::updateUserPosition($userLocation);
                    return TRUE;
                }
            }
        }

        $locationSearch = $this->param('locationSearch');

        if (isset($locationSearch)) {
            $collection = Geocoder::geocode($locationSearch);

            if ($collection AND !$collection->isEmpty()) {
                $userLocation = $collection->first();

                if ($userLocation->hasCoordinates()) {
                    Location::updateUserPosition($userLocation);
                    return TRUE;
                }
            }
        }

        return FALSE;
    }

    protected function loadList()
    {
        $sortBy = $orderBy = $this->param('sort_by');

        if (!Location::userPosition()->isValid() AND \Auth::customer()) {
            $customer = \Auth::customer();
            if ($customer->address_id) {
                $address = Addresses_model::find($customer->address_id);
                $searchQuery = $address->address_1.',';
                if ($address->address_2)
                    $searchQuery .= $address->address_2.',';
                $searchQuery .= $address->city.',';
                $searchQuery .= $address->state.',';
                $searchQuery .= $address->postcode;

                $collection = Geocoder::geocode($searchQuery);

                if ($collection AND !$collection->isEmpty()) {
                    $userLocation = $collection->first();

                    if ($userLocation->hasCoordinates()) {
                        Location::updateUserPosition($userLocation);
                    }
                }
            }
        }

        if (!Location::userPosition()->isValid()) {
            try {
                $ip = $_SERVER['REMOTE_ADDR'];

                $details = json_decode(file_get_contents("http://ip-api.com/json/{$ip}"));
                $searchQuery = $details->city.',';
                $searchQuery .= $details->region.',';
                $searchQuery .= $details->zip;

                $collection = Geocoder::geocode($searchQuery);

                if ($collection AND !$collection->isEmpty()) {
                $userLocation = $collection->first();

                if ($userLocation->hasCoordinates()) {
                    Location::updateUserPosition($userLocation);
                }
                }
            } catch(Exception $e) {

            } catch (ErrorException $e) {

            }
        }

        if ($sortBy == 'distance' AND !Location::userPosition()->isValid()) {
            flash()->warning('Could not determine user location')->now();
            $sortBy = null;
        }

        switch ($sortBy) {
            case 'distance':
                $orderBy = 'distance asc';
                break;
            case 'newest':
                $orderBy = 'location_id desc';
                break;
            case 'rating':
                $orderBy = 'reviews_count desc';
                break;
            case 'name':
                $orderBy = 'location_name asc';
                break;
        }

        $options = [
            'page' => $this->param('page'),
            'pageLimit' => $this->param('pageLimit', $this->property('pageLimit')),
            'search' => $this->param('search'),
            'sort' => $orderBy,
        ];

        if ($coordinates = Location::userPosition()->getCoordinates()) {
            $options['latitude'] = $coordinates->getLatitude();
            $options['longitude'] = $coordinates->getLongitude();
        }

        $list = Locations_model::withCount([
            'reviews' => function ($q) {
                $q->isApproved();
            },
        ])->isEnabled()->listFrontEnd($options);

        $this->mapIntoObjects($list);

        if ($sortBy)
            $list->appends('sort_by', $sortBy);

        if ($pageLimit = $this->param('pageLimit'))
            $list->appends('pageLimit', $pageLimit);

        return $list;
    }

    protected function loadFilters()
    {
        $url = page_url().'?';
        if ($filterSearch = input('search')) {
            $url .= 'search='.$filterSearch.'&';
        }

        $filters = [
            'distance' => [
                'name' => lang('igniter.local::default.text_filter_distance'),
                'href' => $url.'sort_by=distance',
            ],
            'newest' => [
                'name' => lang('igniter.local::default.text_filter_newest'),
                'href' => $url.'sort_by=newest',
            ],
            'rating' => [
                'name' => lang('igniter.local::default.text_filter_rating'),
                'href' => $url.'sort_by=rating',
            ],
            'name' => [
                'name' => lang('admin::lang.label_name'),
                'href' => $url.'sort_by=name',
            ],
        ];

        return $filters;
    }

    protected function mapIntoObjects($list)
    {
        $collection = $list->getCollection()->map(function ($location) {
            return $this->createLocationObject($location);
        });

        $list->setCollection($collection);

        return $list;
    }

    protected function createLocationObject($location)
    {
        $object = new \stdClass();

        $object->name = $location->location_name;
        $object->permalink = $location->permalink_slug;
        $object->address = $location->getAddress();
        $object->reviewsScore = $location->reviews_score();
        $object->reviewsCount = $location->reviews_count;

        $object->distance = ($coordinates = Location::userPosition()->getCoordinates())
            ? $location->calculateDistance($coordinates)
            : null;

        $object->thumb = ($object->hasThumb = $location->hasMedia('thumb'))
            ? $location->getThumb()
            : null;

        $object->orderTypes = $location->availableOrderTypes();

        $object->openingSchedule = $location->newWorkingSchedule('opening');
        $object->deliverySchedule = $object->orderTypes->get(Locations_model::DELIVERY)->getSchedule();
        $object->collectionSchedule = $object->orderTypes->get(Locations_model::COLLECTION)->getSchedule();
        $object->hasDelivery = $location->hasDelivery();
        $object->hasCollection = $location->hasCollection();
        $object->deliveryMinutes = $location->deliveryMinutes();
        $object->collectionMinutes = $location->collectionMinutes();
        $object->openingTime = make_carbon($object->openingSchedule->getOpenTime());
        $object->deliveryTime = make_carbon($object->deliverySchedule->getOpenTime());
        $object->collectionTime = make_carbon($object->collectionSchedule->getOpenTime());

        $object->model = $location;

        return $object;
    }
}
