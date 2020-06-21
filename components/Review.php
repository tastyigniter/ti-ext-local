<?php

namespace Igniter\Local\Components;

use Admin\Models\Reviews_model;
use Admin\Traits\ValidatesForm;
use ApplicationException;
use Exception;
use Igniter\Cart\Classes\OrderManager;
use Igniter\Reservation\Classes\BookingManager;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Location;
use Main\Facades\Auth;

class Review extends \System\Classes\BaseComponent
{
    use ValidatesForm;

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
            'reviewDateFormat' => [
                'label' => 'Date format to display the review date ',
                'type' => 'text',
                'default' => 'DD MMM YY',
            ],
            'reviewableType' => [
                'label' => 'Whether the review form is loaded on an order or reservation page, use by the review form',
                'type' => 'select',
                'default' => 'order',
                'options' => [
                    'order' => 'Leave order reviews',
                    'reservation' => 'Leave reservation reviews',
                ],
            ],
            'reviewableHash' => [
                'label' => 'Review sale identifier(hash), use by the review form',
                'type' => 'text',
                'default' => '{{ :hash }}',
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
        $this->page['reviewDateFormat'] = $this->property('reviewDateFormat');
        $this->page['reviewRatingHints'] = $this->getHints();
        $this->page['reviewList'] = $this->loadReviewList();
        $this->page['reviewable'] = $reviewable = $this->loadReviewable();
        $this->page['customerReview'] = $this->loadReview($reviewable);
    }

    public function onLeaveReview()
    {
        try {
            if (!(bool)setting('allow_reviews'))
                throw new ApplicationException(lang('igniter.local::default.review.alert_review_disabled'));

            if (!$customer = Auth::customer())
                throw new ApplicationException(lang('igniter.local::default.review.alert_expired_login'));

            $reviewable = $this->getReviewable();
            if (!$reviewable OR !$reviewable->isCompleted())
                throw new ApplicationException(lang('igniter.local::default.review.alert_review_status_history'));

            if ($this->checkReviewableExists($reviewable))
                throw new ApplicationException(lang('igniter.local::default.review.alert_review_duplicate'));

            $data = post();

            $rules = [
                ['rating.quality', 'lang:igniter.local::default.review.label_quality', 'required|integer'],
                ['rating.delivery', 'lang:igniter.local::default.review.label_delivery', 'required|integer'],
                ['rating.service', 'lang:igniter.local::default.review.label_service', 'required|integer'],
                ['review_text', 'lang:igniter.local::default.review.label_review', 'required|min:2|max:1028'],
            ];

            $this->validate($data, $rules);

            $model = new Reviews_model();
            $model->location_id = $reviewable->location_id;
            $model->customer_id = $customer->customer_id;
            $model->author = $customer->full_name;
            $model->sale_id = $reviewable->getKey();
            $model->sale_type = $reviewable->getMorphClass();
            $model->quality = array_get($data, 'rating.quality');
            $model->delivery = array_get($data, 'rating.delivery');
            $model->service = array_get($data, 'rating.service');
            $model->review_text = array_get($data, 'review_text');
            $model->review_status = (setting('approve_reviews') === 1) ? 1 : 0;

            $model->save();

            flash()->success(lang('igniter.local::default.review.alert_review_success'))->now();

            return Redirect::back();
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->alert($ex->getMessage())->important();
        }
    }

    /**
     * @return mixed
     */
    protected function getHints()
    {
        return array_get(setting('ratings'), 'ratings', []);
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

    protected function loadReviewable()
    {
        $reviewable = $this->getReviewable();

        if (!$reviewable OR !$reviewable->isCompleted())
            return null;

        return $reviewable;
    }

    protected function loadReview($reviewable)
    {
        if (!$reviewable)
            return null;

        return Reviews_model::whereReviewable($reviewable)->first();
    }

    protected function getReviewable()
    {
        $reviewableHash = $this->param('hash', $this->property('reviewableHash'));

        $reviewable = null;
        if ($this->property('reviewableType') == 'reservation') {
            $reviewable = BookingManager::instance()->getReservationByHash($reviewableHash, Auth::customer());
        }
        else if ($this->property('reviewableType') == 'order') {
            $reviewable = OrderManager::instance()->getOrderByHash($reviewableHash, Auth::customer());
        }

        return $reviewable;
    }

    protected function checkReviewableExists($reviewable)
    {
        if (!$customer = Auth::customer())
            return FALSE;

        return Reviews_model::checkReviewed($reviewable, $customer);
    }
}