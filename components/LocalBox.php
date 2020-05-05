<?php namespace Igniter\Local\Components;

use Admin\Models\Locations_model;
use App;
use ApplicationException;
use DateTime;
use Exception;
use Igniter\Local\Classes\CoveredAreaCondition;
use Illuminate\Support\Collection;
use Redirect;
use Request;

class LocalBox extends \System\Classes\BaseComponent
{
    use \Igniter\Local\Traits\SearchesNearby;
    use \Main\Traits\UsesPage;

    /**
     * @var \Igniter\Local\Classes\Location
     */
    protected $location;

    protected $userPosition;

    protected $asapOrderTime;

    protected $locationCurrent;

    public function initialize()
    {
        $this->location = App::make('location');
    }

    public function defineProperties()
    {
        return [
            'paramFrom' => [
                'type' => 'text',
                'default' => 'location',
            ],
            'redirect' => [
                'label' => 'lang:igniter.local::default.label_redirect',
                'type' => 'select',
                'options' => [static::class, 'getThemePageOptions'],
                'default' => 'home',
            ],
            'defaultOrderType' => [
                'label' => 'lang:igniter.local::default.label_default_order_type',
                'type' => 'select',
                'default' => Locations_model::DELIVERY,
                'options' => [
                    Locations_model::DELIVERY => 'lang:igniter.local::default.text_delivery',
                    Locations_model::COLLECTION => 'lang:igniter.local::default.text_collection',
                ],
            ],
            'hideSearch' => [
                'label' => 'lang:igniter.local::default.label_location_search_mode',
                'type' => 'switch',
                'comment' => 'lang:igniter.local::default.help_location_search_mode',
            ],
            'showLocalThumb' => [
                'label' => 'lang:igniter.local::default.label_show_local_image',
                'type' => 'switch',
                'default' => FALSE,
            ],
            'localThumbWidth' => [
                'label' => 'lang:igniter.local::default.label_local_image_width',
                'type' => 'number',
                'span' => 'left',
                'default' => 80,
            ],
            'localThumbHeight' => [
                'label' => 'lang:igniter.local::default.label_local_image_height',
                'type' => 'number',
                'span' => 'right',
                'default' => 80,
            ],
            'menusPage' => [
                'label' => 'lang:igniter.local::default.label_menu_page',
                'type' => 'select',
                'options' => [static::class, 'getThemePageOptions'],
                'default' => 'local/menus',
            ],
            'localBoxTimeFormat' => [
                'label' => 'Time format for the open and close time',
                'type' => 'text',
                'span' => 'left',
                'default' => 'hh:mm a',
            ],
            'openingTimeFormat' => [
                'label' => 'Time format for the opening later time',
                'type' => 'text',
                'span' => 'left',
                'default' => 'ddd hh:mm a',
            ],
            'timePickerDateFormat' => [
                'label' => 'Date format for the timepicker',
                'type' => 'text',
                'span' => 'left',
                'default' => 'ddd DD',
            ],
            'timePickerDateTimeFormat' => [
                'label' => 'DateTime format for the timepicker',
                'type' => 'text',
                'span' => 'left',
                'default' => 'ddd DD hh:mm a',
            ],
            'cartBoxAlias' => [
                'label' => 'Specify the CartBox component alias used to reload the cart contents after the order type changes',
                'type' => 'text',
                'default' => 'cartBox',
            ],
        ];
    }

    public function onRun()
    {
        $this->addJs('js/local.js', 'local-js');
        $this->addJs('js/local.timeslot.js', 'local-timeslot-js');

        $this->updateCurrentOrderType();

        if ($redirect = $this->redirectForceCurrent()) {
            flash()->error(lang('igniter.local::default.alert_location_required'));

            return $redirect;
        }

        $this->prepareVars();
    }

    public function getAreaConditionLabels()
    {
        return $this->location->coveredArea()->listConditions()->map(function (CoveredAreaCondition $condition) {
            return ucfirst(strtolower($condition->getLabel()));
        })->all();
    }

    public function onChangeOrderType()
    {
        try {
            if (!$location = $this->location->current())
                throw new ApplicationException(lang('igniter.local::default.alert_location_required'));

            if (!$this->location->checkOrderType($orderType = post('type')))
                throw new ApplicationException(lang('igniter.local::default.alert_'.$orderType.'_unavailable'));

            $this->location->updateOrderType($orderType);

            $this->controller->pageCycle();

            $cartBox = $this->controller->findComponentByAlias($this->property('cartBoxAlias'));

            if ($cartBox AND $cartBox->property('pageIsCheckout'))
                return Redirect::to($this->controller->pageUrl($this->property('checkoutPage')));

            return array_merge($cartBox->fetchPartials(), $this->fetchPartials());
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->danger($ex->getMessage())->now();
        }
    }

    public function onSetOrderTime()
    {
        try {
            if (!is_numeric($timeIsAsap = post('asap')))
                throw new ApplicationException('Please select a slot type.');

            if (!strlen($timeSlotDate = post('date')))
                throw new ApplicationException('Please select a slot date.');

            if (!strlen($timeSlotTime = post('time')) AND !$timeIsAsap)
                throw new ApplicationException('Please select a slot time.');

            if (!$location = $this->location->current())
                throw new ApplicationException(lang('igniter.local::default.alert_location_required'));

            $timeSlotDateTime = $timeIsAsap
                ? $this->location->asapScheduleTimeslot()
                : make_carbon($timeSlotDate.' '.$timeSlotTime);

            if (!$this->location->checkOrderTime($timeSlotDateTime))
                throw new ApplicationException(lang('igniter.local::default.alert_'.$this->location->orderType().'_unavailable'));

            $this->location->updateScheduleTimeSlot($timeSlotDateTime, $timeIsAsap);

            $this->controller->pageCycle();

            return $this->fetchPartials();
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->danger($ex->getMessage())->now();
        }
    }

    protected function prepareVars()
    {
        $this->page['hideSearch'] = $this->property('hideSearch', FALSE);
        $this->page['showReviews'] = setting('allow_reviews') == 1;
        $this->page['showLocalThumb'] = $this->property('showLocalThumb', FALSE);
        $this->page['localThumbWidth'] = $this->property('localThumbWidth');
        $this->page['localThumbHeight'] = $this->property('localThumbHeight');
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['searchEventHandler'] = $this->getEventHandler('onSearchNearby');
        $this->page['timeSlotEventHandler'] = $this->getEventHandler('onSetOrderTime');
        $this->page['orderTypeEventHandler'] = $this->getEventHandler('onChangeOrderType');
        $this->page['localBoxTimeFormat'] = $this->property('localBoxTimeFormat');
        $this->page['openingTimeFormat'] = $this->property('openingTimeFormat');
        $this->page['timePickerDateFormat'] = $this->property('timePickerDateFormat');
        $this->page['timePickerDateTimeFormat'] = $this->property('timePickerDateTimeFormat');

        $this->page['location'] = $this->location;
        $this->page['locationCurrent'] = $this->location->current();
        $this->page['locationTimeslot'] = $this->parseTimeslot($this->location->scheduleTimeslot());
    }

    public function fetchPartials()
    {
        $this->prepareVars();

        return [
            '#notification' => $this->renderPartial('flash'),
            '#local-timeslot' => $this->renderPartial('@timeslot'),
            '#local-control' => $this->renderPartial('@control'),
            '#local-box-two' => $this->renderPartial('@box_two'),
        ];
    }

    protected function parseTimeslot(Collection $timeslot)
    {
        $parsed = ['dates' => [], 'hours' => []];

        $timeslot->collapse()->each(function (DateTime $slot) use (&$parsed) {
            $dateKey = $slot->format('Y-m-d');
            $hourKey = $slot->format('H:i');
            $dateValue = make_carbon($slot)->isoFormat($this->property('timePickerDateFormat'));
            $hourValue = make_carbon($slot)->isoFormat($this->property('openingTimeFormat'));

            $parsed['dates'][$dateKey] = $dateValue;
            $parsed['hours'][$dateKey][$hourKey] = $hourValue;
        });

        ksort($parsed['dates']);
        ksort($parsed['hours']);

        return $parsed;
    }

    protected function redirectForceCurrent()
    {
        if ($this->location->current())
            return;

        return Redirect::to($this->controller->pageUrl($this->property('redirect')));
    }

    protected function updateCurrentOrderType()
    {
        if (!$locationCurrent = $this->location->current())
            return;

        // Makes sure the current active order type is offered by the location.
        if (in_array($this->location->orderType(), $locationCurrent->availableOrderTypes()))
            return;

        $this->location->updateOrderType(
            $this->property('defaultOrderType', Locations_model::DELIVERY)
        );
    }
}
