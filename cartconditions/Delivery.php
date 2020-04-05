<?php

namespace Igniter\Local\CartConditions;

use Admin\Models\Locations_model;
use Igniter\Flame\Cart\CartCondition;
use Location;

class Delivery extends CartCondition
{
    public $priority = 100;

    protected $deliveryCharge = 0;

    protected $minimumOrder = 0;

    public function beforeApply()
    {
        // Do not apply condition when orderType is not delivery
        if (Location::orderType() != Locations_model::DELIVERY)
            return FALSE;

        $coveredArea = Location::coveredArea();
        $cartSubtotal = $this->getCartContent()->subtotal();
        $this->deliveryCharge = $coveredArea->deliveryAmount($cartSubtotal);
        $this->minimumOrder = (float)$coveredArea->minimumOrderTotal($cartSubtotal);
    }

    public function getRules()
    {
        return ["subtotal > {$this->minimumOrder}"];
    }

    public function getActions()
    {
        return [
            ['value' => "+{$this->deliveryCharge}"],
        ];
    }

    public function getValue()
    {
        return $this->calculatedValue > 0 ? $this->calculatedValue : lang('main::lang.text_free');
    }

    public function whenInValid()
    {
        if (!$this->getCartContent()->subtotal())
            return;

        flash()->warning(sprintf(
            lang('igniter.cart::default.alert_min_delivery_order_total'),
            currency_format($this->minimumOrder)
        ))->now();
    }
}