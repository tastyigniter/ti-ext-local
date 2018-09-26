<?php

namespace Igniter\Local\Components;

use Admin\Models\Reviews_model;
use Location;
use Redirect;
use System\Models\Settings_model;

class Review extends \System\Classes\BaseComponent
{
    public $defaultPartial = 'list';

    public function defineProperties()
    {
        return [
            'pageLimit' => [
                'label' => 'Reviews Per Page',
                'type' => 'number',
                'default' => 20,
            ],
            'sort' => [
                'label' => 'Sort reviews list by',
                'type' => 'text',
                'default' => 'date_added asc',
            ],
            'dateFormat' => [
                'label' => 'Review date format',
                'type' => 'text',
                'default' => 'd M y H:i',
            ],
            'redirectPage' => [
                'label' => 'Page to redirect to when reviews is disabled',
                'type' => 'string',
                'default' => 'local/menus',
            ],
        ];
    }

    public function initialize()
    {
        $this->addCss('~/app/admin/formwidgets/starrating/assets/vendor/raty/jquery.raty.css', 'jquery-raty-css');
        $this->addJs('~/app/admin/formwidgets/starrating/assets/vendor/raty/jquery.raty.js', 'jquery-raty-js');

        $this->addCss('~/app/admin/formwidgets/starrating/assets/css/starrating.css', 'starrating-css');
        $this->addJs('~/app/admin/formwidgets/starrating/assets/js/starrating.js', 'starrating-js');
    }

    public function onRun()
    {
        if (!setting('allow_reviews')) {
            flash()->error(lang('igniter.local::default.review.alert_review_disabled'))->now();

            return Redirect::to($this->controller->pageUrl($this->property('redirectPage')));
        }

        $this->id = uniqid($this->alias);
        $this->page['reviewDateFormat'] = $this->property('dateFormat');
        $this->page['reviewRatingHints'] = $this->getHints();
        $this->page['reviewList'] = $this->loadReviewList();
    }

    protected function loadReviewList()
    {
        if (!$location = Location::current())
            return null;

        $list = Reviews_model::with(['customer', 'customer.address'])->listFrontEnd([
            'page' => $this->param('page'),
            'pageLimit' => $this->property('pageLimit'),
            'sort' => $this->property('sort', 'date_added asc'),
            'location' => $location->getKey(),
        ]);

        return $list;
    }

    /**
     * @return mixed
     */
    protected function getHints()
    {
        $result = Settings_model::where('sort', 'ratings')->first();

        return array_get($result->value, 'ratings', []);
    }
}