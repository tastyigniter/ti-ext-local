<?php

namespace Igniter\Local\CartConditions;

use Igniter\Cart\CartCondition;
use Igniter\Cart\Facades\Cart;
use Igniter\Local\Facades\Location;
use Igniter\Local\Models\Location as LocationModel;

class Delivery extends CartCondition
{
    public $priority = 100;

    protected $deliveryCharge = 0;

    public function beforeApply()
    {
        // Do not apply condition when orderType is not delivery
        if (Location::orderType() != LocationModel::DELIVERY) {
            return false;
        }

        $cartSubtotal = Cart::subtotal();
        $this->deliveryCharge = Location::coveredArea()->deliveryAmount($cartSubtotal);
    }

    public function getRules()
    {
        return [
            "{$this->deliveryCharge} >= 0",
        ];
    }

    public function getActions()
    {
        return [
            ['value' => "+{$this->deliveryCharge}"],
        ];
    }

    public function getValue()
    {
        return $this->calculatedValue > 0 ? $this->calculatedValue : lang('igniter::main.text_free');
    }
}
