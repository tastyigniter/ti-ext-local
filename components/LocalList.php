<?php

namespace SamPoyigi\Local\Components;

class LocalList extends \System\Classes\BaseComponent
{
    public $isHidden = TRUE;

    protected $list = [];

    public function onRun()
    {
        $this->list = $this->loadList();
    }

    public function onRender()
    {
        $this->id = uniqid($this->alias);
        $this->page['locations'] = $this->prepareList();
        $this->page['distanceUnit'] = config_item('distance_unit');
        $this->page['filterSearch'] = $this->input->post_get('search');
        $this->page['filterSorted'] = $this->input->post_get('sort_by');
        $this->page['filterSorters'] = $this->loadFilters();
    }

    protected function prepareList()
    {
        $locations = $this->list->get();

        $locations->each(function ($location) {
            $location->applyLocationClass();
        });

        return $locations;
    }

    protected function loadList()
    {
        if (!$library = $this->property('library'))
            throw new \Exception("Missing [location library] property in {$this->alias} component");

        $model = $library->getModel();
        $sortBy = $this->param('sort_by');
        $userPosition = $library->area()->userPosition();

        if ($sortBy == 'distance' AND (!$userPosition->latitude OR !$userPosition->longitude)) {
            $this->alert->warning_now('Could not determine user location');
            $sortBy = null;
        }

        switch ($sortBy) {
            case 'distance':
                $sortBy = 'distance asc';
                break;
            case 'newest':
                $sortBy = 'location_id desc';
                break;
            case 'rating':
                $sortBy = 'reviews_count desc';
                break;
            case 'name':
                $sortBy = 'location_name asc';
                break;
        }

        $list = $model->with([
            'working_hours',
            'delivery_areas',
//            'customer',
        ])->withCount([
            'reviews' => function ($q) {
                $q->isApproved();
            },
        ])->listFrontEnd([
            'page'      => $this->param('page'),
            'pageLimit' => $this->property('pageLimit', config_item('menus_page_limit')),
            'search'    => $this->param('search'),
            'sort'      => $sortBy,
            'latitude'  => $userPosition->latitude,
            'longitude' => $userPosition->longitude,
        ]);

        return $list;
    }

    protected function loadFilters()
    {
        $url = '';
        if ($filterSearch = $this->input->get('search')) {
            $url .= 'search='.$filterSearch.'&';
        }

        $filters = [
            'distance' => [
                'name' => lang('sampoyigi.local::default.text_filter_distance'),
                'href' => 'local/all?'.$url.'sort_by=distance',
            ],
            'newest'   => [
                'name' => lang('sampoyigi.local::default.text_filter_newest'),
                'href' => 'local/all?'.$url.'sort_by=newest',
            ],
            'rating'   => [
                'name' => lang('sampoyigi.local::default.text_filter_rating'),
                'href' => 'local/all?'.$url.'sort_by=rating',
            ],
            'name'     => [
                'name' => lang('sampoyigi.local::default.text_filter_name'),
                'href' => 'local/all?'.$url.'sort_by=name',
            ],
        ];

        return $filters;
    }
}