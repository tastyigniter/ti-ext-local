<?php

namespace Igniter\Local\Components;

use Admin\Models\Locations_model;
use App;
use ApplicationException;
use Carbon\Carbon;
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

    protected $locationCurrent;

    protected $currentSchedule;

    public function initialize()
    {
        $this->location = App::make('location');
        $this->location->current()->loadCount([
            'reviews' => function ($q) {
                $q->isApproved();
            },
        ]);
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
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
            'defaultOrderType' => [
                'label' => 'lang:igniter.local::default.label_default_order_type',
                'type' => 'select',
                'default' => Locations_model::DELIVERY,
                'options' => [
                    Locations_model::DELIVERY => 'lang:igniter.local::default.text_delivery',
                    Locations_model::COLLECTION => 'lang:igniter.local::default.text_collection',
                ],
                'validationRule' => 'required|in:delivery,collection',
            ],
            'hideSearch' => [
                'label' => 'lang:igniter.local::default.label_location_search_mode',
                'type' => 'switch',
                'comment' => 'lang:igniter.local::default.help_location_search_mode',
                'validationRule' => 'required|boolean',
            ],
            'showLocalThumb' => [
                'label' => 'lang:igniter.local::default.label_show_local_image',
                'type' => 'switch',
                'default' => FALSE,
                'validationRule' => 'required|boolean',
            ],
            'localThumbWidth' => [
                'label' => 'lang:igniter.local::default.label_local_image_width',
                'type' => 'number',
                'span' => 'left',
                'default' => 80,
                'validationRule' => 'integer',
            ],
            'localThumbHeight' => [
                'label' => 'lang:igniter.local::default.label_local_image_height',
                'type' => 'number',
                'span' => 'right',
                'default' => 80,
                'validationRule' => 'integer',
            ],
            'menusPage' => [
                'label' => 'lang:igniter.local::default.label_menu_page',
                'type' => 'select',
                'options' => [static::class, 'getThemePageOptions'],
                'default' => 'local/menus',
                'validationRule' => 'regex:/^[a-z0-9\-_\/]+$/i',
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

            return Redirect::back();
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
                throw new ApplicationException(lang('igniter.local::default.alert_slot_type_required'));

            if (!strlen($timeSlotDate = post('date')) AND !$timeIsAsap)
                throw new ApplicationException(lang('igniter.local::default.alert_slot_date_required'));

            if (!strlen($timeSlotTime = post('time')) AND !$timeIsAsap)
                throw new ApplicationException(lang('igniter.local::default.alert_slot_time_required'));

            if (!$location = $this->location->current())
                throw new ApplicationException(lang('igniter.local::default.alert_location_required'));

            $timeSlotDateTime = $timeIsAsap
                ? Carbon::now()
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
        $this->page['showLocalThumb'] = $this->property('showLocalThumb', FALSE);
        $this->page['localThumbWidth'] = $this->property('localThumbWidth');
        $this->page['localThumbHeight'] = $this->property('localThumbHeight');
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['searchEventHandler'] = $this->getEventHandler('onSearchNearby');
        $this->page['timeSlotEventHandler'] = $this->getEventHandler('onSetOrderTime');
        $this->page['orderTypeEventHandler'] = $this->getEventHandler('onChangeOrderType');
        $this->page['localBoxTimeFormat'] = lang('system::lang.moment.time_format');
        $this->page['openingTimeFormat'] = lang('system::lang.moment.day_time_format_short');
        $this->page['timePickerDateFormat'] = lang('system::lang.moment.day_format');
        $this->page['timePickerDateTimeFormat'] = lang('system::lang.moment.day_time_format');

        $this->page['location'] = $this->location;
        $this->page['locationCurrent'] = $this->location->current();
        $this->page['locationTimeslot'] = $this->parseTimeslot($this->location->scheduleTimeslot());
        $this->page['locationCurrentSchedule'] = $this->location->workingSchedule($this->location->orderType());
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
            $dateValue = make_carbon($slot)->isoFormat(lang('system::lang.moment.day_format'));
            $hourValue = make_carbon($slot)->isoFormat(lang('system::lang.moment.time_format'));

            $parsed['dates'][$dateKey] = $dateValue;
            $parsed['hours'][$dateKey][$hourKey] = $hourValue;
        });

        ksort($parsed['dates']);
        ksort($parsed['hours']);

        return $parsed;
    }

    protected function redirectForceCurrent()
    {
        if ($this->location->current() && $this->location->current()->location_status == 1)
            return;

        return Redirect::to($this->controller->pageUrl($this->property('redirect')));
    }

    protected function updateCurrentOrderType()
    {
        if (!$locationCurrent = $this->location->current())
            return;

        $defaultOrderType = $this->property('defaultOrderType', Locations_model::DELIVERY);
        if (!in_array($defaultOrderType, $locationCurrent->availableOrderTypes()))
            return;

        if (!$this->location->getSession('orderType'))
            $this->location->updateOrderType($defaultOrderType);
    }
}
