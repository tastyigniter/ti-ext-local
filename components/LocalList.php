<?php

namespace Igniter\Local\Components;

use Admin\Models\Locations_model;
use Igniter\Local\Traits\SearchesNearby;
use Location;

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
        $this->id = uniqid($this->alias);
        $this->page['distanceUnit'] = $this->property('distanceUnit', setting('distance_unit'));
        $this->page['openingTimeFormat'] = lang('system::lang.moment.day_time_format_short');
        $this->page['filterSearch'] = input('search', $this->getSearchQuery());
        $this->page['filterSorted'] = input('sort_by');
        $this->page['filterSorters'] = $this->loadFilters();

        $this->page['userPosition'] = Location::userPosition();

        $this->page['locationsList'] = $this->loadList();
    }

    protected function loadList()
    {
        $sortBy = $orderBy = $this->param('sort_by');

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

        $object->openingSchedule = $location->newWorkingSchedule('opening');
        $object->deliverySchedule = $location->newWorkingSchedule('delivery');
        $object->collectionSchedule = $location->newWorkingSchedule('collection');
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
