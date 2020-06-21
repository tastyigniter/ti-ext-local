<?php

namespace Igniter\Local\Classes;

class CoveredAreaCondition
{
    public $type;

    public $amount;

    public $total;

    public $priority;

    public function __construct(array $condition = [])
    {
        $this->type = array_get($condition, 'type', 'all');
        $this->amount = (float)array_get($condition, 'amount', -1);
        $this->total = (float)array_get($condition, 'total', 0);
        $this->priority = (int)array_get($condition, 'priority', 999);
    }

    public function getLabel()
    {
        $condition['amount'] = lang('main::lang.text_free');
        if ($this->amount < 0) {
            $condition['amount'] = lang('igniter.local::default.text_delivery_not_available');
        }
        elseif ($this->amount > 0) {
            $condition['amount'] = currency_format($this->amount);
        }

        $condition['total'] = $this->total
            ? currency_format($this->total)
            : lang('igniter.local::default.text_delivery_all_orders');

        $type = $this->type == 'all' ? 'all_orders' : $this->type.'_total';
        $label = lang('igniter.local::default.text_condition_'.$type);

        return parse_values($condition, $label);
    }

    public function getCharge()
    {
        return ($this->amount < 0) ? null : $this->amount;
    }

    public function getMinTotal()
    {
        return $this->amount;
    }

    public function isValid($cartTotal)
    {
        if ($this->type === 'below')
            return $cartTotal < $this->total;

        if ($this->type === 'above')
            return $cartTotal >= $this->total;

        return TRUE;
    }
}