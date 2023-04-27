<?php

namespace Igniter\Local\ScheduleTypes;

use Igniter\Cart\Facades\Cart;
use Igniter\Local\Classes\AbstractOrderType;
use Igniter\Local\Facades\Location as LocationFacade;

class Delivery extends AbstractOrderType
{
    public function getOpenDescription(): string
    {
        return sprintf(
            lang('igniter.local::default.text_delivery_time_info'),
            sprintf(lang('igniter.local::default.text_in_minutes'), $this->getLeadTime())
        );
    }

    public function getOpeningDescription(string $format): string
    {
        $starts = make_carbon($this->getSchedule()->getOpenTime());

        return sprintf(
            lang('igniter.local::default.text_delivery_time_info'),
            sprintf(lang('igniter.local::default.text_starts'), '<b>'.$starts->isoFormat($format).'</b>')
        );
    }

    public function getClosedDescription(): string
    {
        return sprintf(
            lang('igniter.local::default.text_delivery_time_info'),
            lang('igniter.local::default.text_is_closed')
        );
    }

    public function getDisabledDescription(): string
    {
        return lang('igniter.local::default.text_delivery_is_disabled');
    }

    public function isActive(): bool
    {
        return $this->code === LocationFacade::orderType();
    }

    public function isDisabled(): bool
    {
        return !$this->model->hasDelivery();
    }
}
