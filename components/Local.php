<?php namespace SamPoyigi\Local\Components;

use Cart;
use Location;

class Local extends \System\Classes\BaseComponent
{
    use \SamPoyigi\Local\Traits\SearchesNearby;

    protected $userPosition;

    protected $currentLocation;

    public function defineProperties()
    {
        return [
            'paramFrom'      => [
                'type'    => 'text',
                'default' => 'location',
            ],
            'showLocalThumb' => [
                'label'   => 'lang:sampoyigi.local::default.label_show_menu_button',
                'type'    => 'switch',
                'default' => FALSE,
            ],
            'menusPage'      => [
                'label'   => 'lang:sampoyigi.local::default.label_menus_page',
                'type'    => 'text',
                'default' => 'local/menus',
            ],
            'timeFormat'     => [
                'label'   => 'Time format',
                'type'    => 'text',
                'default' => 'H:i a',
            ],
        ];
    }

    public function onRun()
    {
        $this->addCss('stylesheet.css', 'local-module-css');
        $this->addJs('local.js', 'local-module-js');

        if (strlen($paramFrom = $this->property('paramFrom'))) {
            $this->overrideLocalFromParam($paramFrom);
        }

        $this->page['userPosition'] = $this->userPosition = Location::userPosition();
        $this->page['currentLocation'] = $this->currentLocation = Location::current();
        if ($this->currentLocation AND $this->userPosition) {
            if ($area = $this->currentLocation->findOrFirstDeliveryArea($this->userPosition))
                Location::setCoveredArea($area);
        }

        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['showLocalThumb'] = $this->property('showLocalThumb', FALSE);
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['openingTimeFormat'] = $this->property('timeFormat');
        $this->page['searchEventHandler'] = $this->getEventHandler('onSearchNearby');

        $this->page['orderType'] = Location::orderType();
        $this->page['requiresUserPosition'] = Location::requiresUserPosition();
        $this->page['userPositionIsCovered'] = Location::checkDeliveryCoverage() != 'outside';

        $this->page['deliveryConditionText'] = $this->translateConditionSummary();

        $this->page['hasDelivery'] = $this->currentLocation->hasDelivery();
        $this->page['hasCollection'] = $this->currentLocation->hasCollection();

        $this->page['isOpened'] = Location::isOpened();
        $this->page['isClosed'] = Location::isClosed();
        $this->page['openingType'] = $this->currentLocation->workingHourType('opening');
        $this->page['openingSchedule'] = Location::workingSchedule('opening');
    }

    protected function overrideLocalFromParam($paramFrom)
    {
        $param = $this->param($paramFrom);

        if (!$model = Location::getBySlug($param))
            return;

        Location::setModel($model);
    }

    protected function translateConditionSummary()
    {
        $summary = [];
        foreach (Location::getDeliveryChargeConditions() as $condition) {

            $condition['amount'] = !empty($condition['amount']) ? currency_format($condition['amount']) : lang('sampoyigi.local::default.text_free');
            $condition['total'] = !empty($condition['total']) ? currency_format($condition['total']) : lang('sampoyigi.local::default.text_delivery_all_orders');

            $summary[] = parse_values($condition, $condition['label']);
        }

        return implode(" ", $summary);
    }
}
