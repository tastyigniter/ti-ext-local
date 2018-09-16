<?php

namespace Igniter\Local\Conditions;

use Cart;
use Igniter\Flame\Cart\CartCondition;
use Location;

class Delivery extends CartCondition
{
    protected $deliveryCharge;

    protected $minimumOrder;

    public function onLoad()
    {
        $coveredArea = Location::coveredArea();
        $this->deliveryCharge = $coveredArea->deliveryAmount(Cart::subtotal());
        $this->minimumOrder = (float)$coveredArea->minimumOrderTotal(Cart::subtotal());
    }

    public function beforeApply()
    {
        // Do not apply condition when orderType is not delivery
        if (Location::orderType() != 'delivery')
            return FALSE;
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

    public function whenInValid()
    {
        flash()->warning(sprintf(
            lang('igniter.cart::default.alert_min_delivery_order_total'),
            currency_format($this->minimumOrder)
        ))->now();
    }
}