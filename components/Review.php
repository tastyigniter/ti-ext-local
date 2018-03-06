<?php

namespace SamPoyigi\Local\Components;

use Location;
use Admin\Models\Reviews_model;

class Review extends \System\Classes\BaseComponent
{
    public $defaultPartial = 'list';

    public $isHidden = TRUE;

    public function onRun()
    {
        $this->id = uniqid($this->alias);
        $this->page['reviewList'] = $this->loadReviewList();
    }

    protected function loadReviewList()
    {
        if (!$location = Location::current())
            return null;

        $list = Reviews_model::with([
            'location',
            'customer',
        ])->listFrontEnd([
            'page'      => $this->param('page'),
            'pageLimit' => $this->property('pageLimit', setting('main_page_limit')),
            'sort'      => $this->property('sort', 'date_added asc'),
            'location'  => $location->getKey(),
        ]);

        return $list;
    }
}