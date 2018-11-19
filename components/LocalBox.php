<?php namespace Igniter\Local\Components;

use App;
use ApplicationException;
use Carbon\Carbon;
use Exception;
use Igniter\Local\Classes\Location;
use Illuminate\Support\Collection;
use Main\Template\Page;
use Redirect;
use Request;

class LocalBox extends \System\Classes\BaseComponent
{
    use \Igniter\Local\Traits\SearchesNearby;

    /**
     * @var \Igniter\Local\Classes\Location
     */
    protected $location;

    protected $userPosition;

    protected $asapOrderTime;

    protected $locationCurrent;

    public function initialize()
    {
        $this->location = App::make(Location::class);
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
                'type' => 'text',
                'default' => 'home',
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

        if (strlen($paramFrom = $this->property('paramFrom'))) {
            $this->overrideLocalFromParam($paramFrom);
        }

        $this->bootLocation();

        if ($redirect = $this->redirectForceCurrent()) {
            flash()->error(lang('igniter.local::default.alert_location_required'));
            return $redirect;
        }

        $this->prepareVars();
    }

    public function deliveryConditionText()
    {
        $summary = [];
        foreach ($this->location->getDeliveryChargeConditions() as $condition) {
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

        return implode(', ', $summary);
    }

    public function onSetOrderTime()
    {
        try {
            if (!is_numeric($timeIsAsap = post('asap')))
                throw new ApplicationException('Please select a slot type.');

            if (!strlen($timeSlotDate = post('date')))
                throw new ApplicationException('Please select a slot date.');

            $timeSlotTime = null;
            if (!$timeIsAsap AND !strlen($timeSlotTime = post('time')))
                throw new ApplicationException('Please select a slot time.');

            if (!$location = $this->location->current())
                throw new ApplicationException(lang('igniter.local::default.alert_location_required'));

            $timeSlotDateTime = make_carbon($timeSlotDate.' '.$timeSlotTime);
            if ($timeIsAsap)
                $timeSlotDateTime = $this->location->scheduleTimeslot()->first();

            if (!$this->location->checkOrderTime($timeSlotDateTime))
                throw new ApplicationException(lang('igniter.local::default.alert_'.$this->location->orderType().'_unavailable'));

            $this->location->updateScheduleTimeSlot($timeSlotDateTime, $timeIsAsap);

            $this->controller->pageCycle();

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
        $this->page['hideSearch'] = $this->property('hideSearch', FALSE);
        $this->page['showLocalThumb'] = $this->property('showLocalThumb', FALSE);
        $this->page['localThumbWidth'] = $this->property('localThumbWidth');
        $this->page['localThumbHeight'] = $this->property('localThumbHeight');
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['searchEventHandler'] = $this->getEventHandler('onSearchNearby');
        $this->page['timeSlotEventHandler'] = $this->getEventHandler('onSetOrderTime');
        $this->page['openingTimeFormat'] = $this->property('openTimeFormat', setting('time_format'));
        $this->page['timePickerDateFormat'] = $this->property('timePickerDateFormat');
        $this->page['timePickerTimeFormat'] = $this->property('timePickerTimeFormat');
        $this->page['orderDateTimeFormat'] = $this->property('timePickerDateTimeFormat',
            setting('date_format').' '.setting('time_format')
        );

        $this->page['location'] = $this->location;
        $this->page['locationCurrent'] = $this->location->current();
        $this->page['locationTimeslot'] = $this->parseTimeslot($this->location->scheduleTimeslot());
    }

    protected function parseTimeslot(Collection $timeslot)
    {
        $parsed = ['dates' => [], 'hours' => []];

        $timeslot->each(function (Carbon $slot) use (&$parsed) {
            $dateKey = $slot->format('Y-m-d');
            $hourKey = $slot->format('H:i');
            $dateValue = $slot->format($this->property('timePickerDateFormat'));
            $hourValue = $slot->format($this->property('timePickerTimeFormat'));

            $parsed['dates'][$dateKey] = $dateValue;
            $parsed['hours'][$dateKey][$hourKey] = $hourValue;
        });

        return $parsed;
    }

    protected function overrideLocalFromParam($paramFrom)
    {
        $param = $this->param($paramFrom);

        if (!$model = $this->location->getBySlug($param))
            return;

        $this->location->setModel($model);
    }

    protected function redirectForceCurrent()
    {
        if ($this->location->current())
            return;

        return Redirect::to($this->controller->pageUrl($this->property('redirect')));
    }

    protected function bootLocation()
    {
        if (
            $locationCurrent = $this->location->current()
            AND $userPosition = $this->location->userPosition()
            AND !$this->location->getAreaId()
        ) {
            $this->location->setCoveredArea(
                $locationCurrent->findOrFirstDeliveryArea($userPosition)
            );
        }

        // Makes sure the current active order type is offered by the location.
        if (in_array($this->location->orderType(), $locationCurrent->availableOrderTypes()))
            return;

        $this->location->updateOrderType($locationCurrent->hasDelivery()
            ? $locationCurrent::DELIVERY
            : $locationCurrent::COLLECTION
        );
    }
}
