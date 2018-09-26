<?php namespace Igniter\Local\Components;

use ApplicationException;
use Carbon\Carbon;
use Exception;
use Location;
use Main\Template\Page;
use Redirect;
use Request;

class LocalBox extends \System\Classes\BaseComponent
{
    use \Igniter\Local\Traits\SearchesNearby;

    protected $userPosition;

    protected $locationCurrent;

    public function defineProperties()
    {
        return [
            'paramFrom' => [
                'type' => 'text',
                'default' => 'location',
            ],
            'redirect' => [
                'label' => 'lang:igniter.local::default.label_redirect',
                'type' => 'text',
                'default' => 'home',
            ],
            'showLocalThumb' => [
                'label' => 'lang:igniter.local::default.label_show_menu_image',
                'type' => 'switch',
                'default' => FALSE,
            ],
            'menusPage' => [
                'label' => 'lang:igniter.local::default.label_menu_page_limit',
                'type' => 'select',
                'default' => 'local/menus',
            ],
            'openTimeFormat' => [
                'label' => 'Time format for the opening time',
                'type' => 'text',
            ],
            'timePickerDateFormat' => [
                'label' => 'Date format for the timepicker',
                'type' => 'text',
                'default' => 'D d',
            ],
            'timePickerTimeFormat' => [
                'label' => 'Time format for the timepicker',
                'type' => 'text',
                'default' => 'H:i',
            ],
            'timePickerDateTimeFormat' => [
                'label' => 'DateTime format for the timepicker',
                'type' => 'text',
            ],
        ];
    }

    public static function getMenusPageOptions()
    {
        return Page::lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->addCss('css/local.css', 'local-css');
        $this->addJs('js/local.js', 'local-js');
        $this->addJs('js/local.timeslot.js', 'local-timeslot-js');

        $this->loadLocation();

        if ($redirect = $this->redirectForceCurrent()) {
            flash()->error(lang('igniter.local::default.alert_location_required'));
            return $redirect;
        }

        $this->prepareVars();
    }

    public function getOrderTimeSlot()
    {
        $generated = [];
        $timeInterval = Location::orderTimeInterval();
        $periods = Location::orderTimePeriods();
        if (!$periods)
            $periods = [];

        foreach ($periods as $date => $workingHours) {
            $weekDate = $workingHours->getWeekDate();

            $weekDateString = $weekDate->format('Y-m-d');
            $generated['dates'][$weekDateString] = $weekDate->format($this->property('timePickerDateFormat'));
            foreach ($workingHours->generateTimes($timeInterval) as $dateTime) {
                if ($workingHours->open->isToday() AND !Carbon::now()->addMinutes($timeInterval)->lte($dateTime))
                    continue;

                $key = $dateTime->format('H:i');
                $generated['hours'][$weekDateString][$key] = $dateTime->format($this->property('timePickerTimeFormat'));
            }
        }

        return $generated;
    }

    public function deliveryConditionText()
    {
        $summary = [];
        foreach (Location::getDeliveryChargeConditions() as $condition) {
            if (empty($condition['amount'])) {
                $condition['amount'] = lang('igniter.local::default.text_free');
            }
            else if ($condition['amount'] < 0) {
                $condition['amount'] = lang('igniter.local::default.text_delivery_not_available');
            }
            else {
                $condition['amount'] = currency_format($condition['amount']);
            }

            $condition['total'] = !empty($condition['total'])
                ? currency_format($condition['total'])
                : lang('igniter.local::default.text_delivery_all_orders');

            $summary[] = ucfirst(strtolower(parse_values($condition, $condition['label'])));
        }

        return implode(" - ", $summary);
    }

    public function onSetOrderTime()
    {
        try {
            if (!strlen($timeSlotType = post('type')))
                throw new ApplicationException('Please select a slot type.');

            if (!strlen($timeSlotDate = post('date')))
                throw new ApplicationException('Please select a slot date.');

            $timeSlotTime = null;
            if ($timeSlotType != 'asap' AND !strlen($timeSlotTime = post('time')))
                throw new ApplicationException('Please select a slot time.');

            if (!$location = Location::current())
                throw new ApplicationException(lang('igniter.local::default.alert_location_required'));

            $timeSlotDateTime = $timeSlotDate.' '.$timeSlotTime;
            if ($timeSlotType == 'asap') {
                $timeSlot = array_get($this->getOrderTimeSlot(), 'hours.'.$timeSlotDate);
                $timeSlotDateTime = $timeSlotDate.' '.key(array_slice($timeSlot, 0, 1));
            }

            $timeSlotDateTime = make_carbon($timeSlotDateTime);

            if (!Location::checkOrderTime($timeSlotDateTime))
                throw new ApplicationException(lang('igniter.local::default.alert_'.Location::orderType().'_unavailable'));

            Location::updateOrderTimeSlot($timeSlotType, $timeSlotDateTime);

            $this->pageCycle();

            return [
                '#notification' => $this->renderPartial('flash'),
                '#local-timeslot' => $this->renderPartial('@timeslot'),
            ];
        }
        catch (Exception $ex) {
            if (Request::ajax()) throw $ex;
            else flash()->danger($ex->getMessage())->now();
        }
    }

    protected function prepareVars()
    {
        $this->page['showLocalThumb'] = $this->property('showLocalThumb', FALSE);
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['searchEventHandler'] = $this->getEventHandler('onSearchNearby');
        $this->page['timeSlotEventHandler'] = $this->getEventHandler('onSetOrderTime');
        $this->page['openingTimeFormat'] = $this->property('openTimeFormat', setting('time_format'));
        $this->page['timePickerDateFormat'] = $this->property('timePickerDateFormat');
        $this->page['timePickerTimeFormat'] = $this->property('timePickerTimeFormat');
        $this->page['orderDateTimeFormat'] = $this->property('timePickerDateTimeFormat',
            setting('date_format').' '.setting('time_format')
        );

        $this->page['location'] = Location::instance();
        $this->page['locationCurrent'] = Location::current();
    }

    protected function overrideLocalFromParam($paramFrom)
    {
        $param = $this->param($paramFrom);

        if (!$model = Location::getBySlug($param))
            return;

        Location::setModel($model);
    }

    protected function redirectForceCurrent()
    {
        if (Location::current())
            return;

        return Redirect::to($this->controller->pageUrl($this->property('redirect')));
    }

    protected function loadLocation()
    {
        if (strlen($paramFrom = $this->property('paramFrom'))) {
            $this->overrideLocalFromParam($paramFrom);
        }

        if (
            $locationCurrent = Location::current()
            AND $userPosition = Location::userPosition()
            AND !Location::getAreaId()
        ) {
            Location::setCoveredArea(
                $locationCurrent->findOrFirstDeliveryArea($userPosition)
            );
        }

        // Makes sure the current active order type is offered by the location.
        if (in_array(Location::orderType(), $locationCurrent->availableOrderTypes()))
            return;

        Location::updateOrderType($locationCurrent->hasDelivery()
            ? $locationCurrent::DELIVERY
            : $locationCurrent::COLLECTION
        );
    }
}
