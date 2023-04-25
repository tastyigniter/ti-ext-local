<?php

namespace Igniter\Local\Listeners;

use Illuminate\Contracts\Events\Dispatcher;

class ExtendLocationOptions
{
    public function subscribe(Dispatcher $dispatcher)
    {
        $dispatcher->listen('admin.locations.defineOptionsFormFields', __CLASS__.'@fields');

        $dispatcher->listen('system.formRequest.extendValidator', __CLASS__.'@extendValidator');
    }

    public function extendValidator($formRequest, $dataHolder)
    {
        if (!$formRequest instanceof \Admin\Requests\Location)
            return;

        $dataHolder->attributes = array_merge($dataHolder->attributes, $this->attributes());

        $dataHolder->rules = array_merge($dataHolder->rules, $this->rules());
    }

    public function fields(): array
    {
        return [
            'limit_orders' => [
                'label' => 'lang:igniter.local::default.label_limit_orders',
                'accordion' => 'lang:admin::lang.locations.text_tab_general_options',
                'type' => 'switch',
                'default' => 0,
                'comment' => 'lang:igniter.local::default.help_limit_orders',
                'span' => 'left',
            ],
            'limit_orders_count' => [
                'label' => 'lang:igniter.local::default.label_limit_orders_count',
                'accordion' => 'lang:admin::lang.locations.text_tab_general_options',
                'type' => 'number',
                'default' => 50,
                'span' => 'right',
                'comment' => 'lang:igniter.local::default.help_limit_orders_interval',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'limit_orders',
                    'condition' => 'checked',
                ],
            ],

            'offer_delivery' => [
                'label' => 'lang:igniter.local::default.label_offer_delivery',
                'accordion' => 'lang:igniter.local::default.text_tab_delivery_order',
                'default' => 1,
                'type' => 'switch',
                'span' => 'left',
            ],
            'delivery_add_lead_time' => [
                'label' => 'lang:igniter.local::default.label_delivery_add_lead_time',
                'accordion' => 'lang:igniter.local::default.text_tab_delivery_order',
                'type' => 'switch',
                'span' => 'right',
                'default' => 0,
                'comment' => 'lang:igniter.local::default.help_delivery_add_lead_time',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_delivery',
                    'condition' => 'checked',
                ],
            ],
            'delivery_time_interval' => [
                'label' => 'lang:igniter.local::default.label_delivery_time_interval',
                'accordion' => 'lang:igniter.local::default.text_tab_delivery_order',
                'default' => 15,
                'type' => 'number',
                'span' => 'left',
                'comment' => 'lang:igniter.local::default.help_delivery_time_interval',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_delivery',
                    'condition' => 'checked',
                ],
            ],
            'delivery_lead_time' => [
                'label' => 'lang:igniter.local::default.label_delivery_lead_time',
                'accordion' => 'lang:igniter.local::default.text_tab_delivery_order',
                'default' => 25,
                'type' => 'number',
                'span' => 'right',
                'comment' => 'lang:igniter.local::default.help_delivery_lead_time',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_delivery',
                    'condition' => 'checked',
                ],
            ],
            'delivery_time_restriction' => [
                'label' => 'lang:igniter.local::default.label_delivery_time_restriction',
                'accordion' => 'lang:igniter.local::default.text_tab_delivery_order',
                'type' => 'radiotoggle',
                'span' => 'right',
                'comment' => 'lang:igniter.local::default.help_delivery_time_restriction',
                'options' => [
                    'lang:admin::lang.text_none',
                    'lang:admin::lang.locations.text_asap_only',
                    'lang:admin::lang.locations.text_later_only',
                ],
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'future_orders[enable_delivery]',
                    'condition' => 'unchecked',
                ],
            ],
            'delivery_cancellation_timeout' => [
                'label' => 'lang:igniter.local::default.label_delivery_cancellation_timeout',
                'accordion' => 'lang:igniter.local::default.text_tab_delivery_order',
                'type' => 'number',
                'span' => 'left',
                'default' => 0,
                'comment' => 'lang:igniter.local::default.help_delivery_cancellation_timeout',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_delivery',
                    'condition' => 'checked',
                ],
            ],
            'delivery_min_order_amount' => [
                'label' => 'lang:igniter.local::default.label_delivery_min_order_amount',
                'accordion' => 'lang:igniter.local::default.text_tab_delivery_order',
                'type' => 'currency',
                'span' => 'right',
                'default' => 0,
                'comment' => 'lang:igniter.local::default.help_delivery_min_order_amount',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_delivery',
                    'condition' => 'checked',
                ],
            ],

            'future_orders[enable_delivery]' => [
                'label' => 'lang:igniter.local::default.label_future_delivery_order',
                'accordion' => 'lang:igniter.local::default.text_tab_delivery_order',
                'type' => 'switch',
                'span' => 'full',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_delivery',
                    'condition' => 'checked',
                ],
            ],
            'future_orders[min_delivery_days]' => [
                'label' => 'lang:igniter.local::default.label_future_min_delivery_days',
                'accordion' => 'lang:igniter.local::default.text_tab_delivery_order',
                'type' => 'number',
                'default' => 0,
                'span' => 'left',
                'comment' => 'lang:igniter.local::default.help_future_min_delivery_days',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'future_orders[enable_delivery]',
                    'condition' => 'checked',
                ],
            ],
            'future_orders[delivery_days]' => [
                'label' => 'lang:igniter.local::default.label_future_delivery_days',
                'accordion' => 'lang:igniter.local::default.text_tab_delivery_order',
                'type' => 'number',
                'default' => 5,
                'span' => 'right',
                'comment' => 'lang:igniter.local::default.help_future_delivery_days',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'future_orders[enable_delivery]',
                    'condition' => 'checked',
                ],
            ],

            'offer_collection' => [
                'label' => 'lang:igniter.local::default.label_offer_collection',
                'accordion' => 'lang:igniter.local::default.text_tab_collection_order',
                'default' => 1,
                'type' => 'switch',
                'span' => 'left',
            ],
            'collection_add_lead_time' => [
                'label' => 'lang:igniter.local::default.label_collection_add_lead_time',
                'accordion' => 'lang:igniter.local::default.text_tab_collection_order',
                'type' => 'switch',
                'span' => 'right',
                'default' => 0,
                'comment' => 'lang:igniter.local::default.help_collection_add_lead_time',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_collection',
                    'condition' => 'checked',
                ],
            ],
            'collection_time_interval' => [
                'label' => 'lang:igniter.local::default.label_collection_time_interval',
                'accordion' => 'lang:igniter.local::default.text_tab_collection_order',
                'default' => 15,
                'type' => 'number',
                'span' => 'left',
                'comment' => 'lang:igniter.local::default.help_collection_time_interval',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_collection',
                    'condition' => 'checked',
                ],
            ],
            'collection_lead_time' => [
                'label' => 'lang:igniter.local::default.label_collection_lead_time',
                'accordion' => 'lang:igniter.local::default.text_tab_collection_order',
                'default' => 25,
                'type' => 'number',
                'span' => 'right',
                'comment' => 'lang:igniter.local::default.help_collection_lead_time',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_collection',
                    'condition' => 'checked',
                ],
            ],
            'collection_time_restriction' => [
                'label' => 'lang:igniter.local::default.label_collection_time_restriction',
                'accordion' => 'lang:igniter.local::default.text_tab_collection_order',
                'type' => 'radiotoggle',
                'span' => 'right',
                'comment' => 'lang:igniter.local::default.help_collection_time_restriction',
                'options' => [
                    'lang:admin::lang.text_none',
                    'lang:admin::lang.locations.text_asap_only',
                    'lang:admin::lang.locations.text_later_only',
                ],
                'trigger' => [
                    'action' => 'disable',
                    'field' => 'future_orders[enable_collection]',
                    'condition' => 'checked',
                ],
            ],
            'collection_cancellation_timeout' => [
                'label' => 'lang:igniter.local::default.label_collection_cancellation_timeout',
                'accordion' => 'lang:igniter.local::default.text_tab_collection_order',
                'type' => 'number',
                'span' => 'left',
                'default' => 0,
                'comment' => 'lang:igniter.local::default.help_collection_cancellation_timeout',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_collection',
                    'condition' => 'checked',
                ],
            ],
            'collection_min_order_amount' => [
                'label' => 'lang:igniter.local::default.label_collection_min_order_amount',
                'accordion' => 'lang:igniter.local::default.text_tab_collection_order',
                'type' => 'currency',
                'span' => 'right',
                'default' => 0,
                'comment' => 'lang:igniter.local::default.help_collection_min_order_amount',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_collection',
                    'condition' => 'checked',
                ],
            ],

            'future_orders[enable_collection]' => [
                'label' => 'lang:igniter.local::default.label_future_collection_order',
                'accordion' => 'lang:igniter.local::default.text_tab_collection_order',
                'type' => 'switch',
                'span' => 'full',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'offer_collection',
                    'condition' => 'checked',
                ],
            ],
            'future_orders[min_collection_days]' => [
                'label' => 'lang:igniter.local::default.label_future_min_collection_days',
                'accordion' => 'lang:igniter.local::default.text_tab_collection_order',
                'type' => 'number',
                'default' => 0,
                'span' => 'left',
                'comment' => 'lang:igniter.local::default.help_future_min_collection_days',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'future_orders[enable_collection]',
                    'condition' => 'checked',
                ],
            ],
            'future_orders[collection_days]' => [
                'label' => 'lang:igniter.local::default.label_future_collection_days',
                'accordion' => 'lang:igniter.local::default.text_tab_collection_order',
                'type' => 'number',
                'default' => 5,
                'span' => 'right',
                'comment' => 'lang:igniter.local::default.help_future_collection_days',
                'trigger' => [
                    'action' => 'enable',
                    'field' => 'future_orders[enable_collection]',
                    'condition' => 'checked',
                ],
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'options.limit_orders' => lang('igniter.local::default.label_limit_orders'),
            'options.limit_orders_count' => lang('igniter.local::default.label_limit_orders_count'),
            'options.offer_delivery' => lang('igniter.local::default.label_offer_delivery'),
            'options.offer_collection' => lang('igniter.local::default.label_offer_collection'),
            'options.offer_reservation' => lang('igniter.local::default.label_offer_collection'),
            'options.delivery_time_interval' => lang('igniter.local::default.label_delivery_time_interval'),
            'options.collection_time_interval' => lang('igniter.local::default.label_collection_time_interval'),
            'options.delivery_lead_time' => lang('igniter.local::default.label_delivery_lead_time'),
            'options.collection_lead_time' => lang('igniter.local::default.label_collection_lead_time'),
            'options.future_orders.enable_delivery' => lang('igniter.local::default.label_future_delivery_order'),
            'options.future_orders.enable_collection' => lang('igniter.local::default.label_future_collection_order'),
            'options.future_orders.min_delivery_days' => lang('igniter.local::default.label_future_min_delivery_days'),
            'options.future_orders.min_collection_days' => lang('igniter.local::default.label_future_min_collection_days'),
            'options.future_orders.delivery_days' => lang('igniter.local::default.label_future_delivery_days'),
            'options.future_orders.collection_days' => lang('igniter.local::default.label_future_collection_days'),
            'options.delivery_time_restriction' => lang('igniter.local::default.label_delivery_time_restriction'),
            'options.collection_time_restriction' => lang('igniter.local::default.label_collection_time_restriction'),
            'options.delivery_cancellation_timeout' => lang('igniter.local::default.label_delivery_cancellation_timeout'),
            'options.collection_cancellation_timeout' => lang('igniter.local::default.label_collection_cancellation_timeout'),
            'options.delivery_add_lead_time' => lang('igniter.local::default.label_delivery_add_lead_time'),
            'options.collection_add_lead_time' => lang('igniter.local::default.label_collection_add_lead_time'),
            'options.delivery_min_order_amount' => lang('igniter.local::default.label_delivery_min_order_amount'),
            'options.collection_min_order_amount' => lang('igniter.local::default.label_collection_min_order_amount'),
        ];
    }

    public function rules(): array
    {
        return [
            'options.limit_orders' => ['boolean'],
            'options.limit_orders_count' => ['integer', 'min:1', 'max:999'],
            'options.offer_delivery' => ['boolean'],
            'options.offer_collection' => ['boolean'],
            'options.offer_reservation' => ['boolean'],
            'options.delivery_time_interval' => ['integer', 'min:5'],
            'options.collection_time_interval' => ['integer', 'min:5'],
            'options.delivery_lead_time' => ['integer', 'min:5'],
            'options.collection_lead_time' => ['integer', 'min:5'],
            'options.future_orders.enable_delivery' => ['boolean'],
            'options.future_orders.enable_collection' => ['boolean'],
            'options.future_orders.min_delivery_days' => ['integer', 'min:0'],
            'options.future_orders.min_collection_days' => ['integer', 'min:0'],
            'options.future_orders.delivery_days' => ['integer', 'min:0', 'gt:options.future_orders.min_delivery_days'],
            'options.future_orders.collection_days' => ['integer', 'min:0', 'gt:options.future_orders.min_collection_days'],
            'options.delivery_time_restriction' => ['nullable', 'integer', 'max:2'],
            'options.collection_time_restriction' => ['nullable', 'integer', 'max:2'],
            'options.delivery_add_lead_time' => ['boolean'],
            'options.collection_add_lead_time' => ['boolean'],
            'options.delivery_cancellation_timeout' => ['integer', 'min:0', 'max:999'],
            'options.collection_cancellation_timeout' => ['integer', 'min:0', 'max:999'],
            'options.delivery_min_order_amount' => ['numeric', 'min:0'],
            'options.collection_min_order_amount' => ['numeric', 'min:0'],
        ];
    }
}
