<?php

namespace Igniter\Local\Classes;

class CoveredAreaCondition
{
    protected $type;

    protected $amount;

    protected $total;

    protected $priority;

    public function __construct(array $condition = [])
    {
        $this->type = array_get($condition, 'type', 'all');
        $this->amount = (float)array_get($condition, 'amount', -1);
        $this->total = (float)array_get($condition, 'total', 0);
        $this->priority = (int)array_get($condition, 'priority', 999);
    }

    public function getLabel()
    {
        $condition['amount'] = $this->amount
            ? currency_format($this->amount)
            : lang($this->amount < 0 ? 'igniter.local::default.text_delivery_not_available' : 'main::lang.text_free');

        $condition['total'] = $this->total
            ? currency_format($this->total)
            : lang('igniter.local::default.text_delivery_all_orders');

        $type = $this->type == 'all' ? 'all_orders' : $this->type.'_total';
        $label = lang('igniter.local::default.text_condition_'.$type);

        return parse_values($condition, $label);
    }
}