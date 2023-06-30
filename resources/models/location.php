<?php

$config['form']['fields'] = [
    'location_name' => [
        'label' => 'lang:igniter::admin.label_name',
        'type' => 'text',
        'span' => 'left',
    ],
    'permalink_slug' => [
        'label' => 'lang:igniter.local::default.label_permalink_slug',
        'type' => 'permalink',
        'span' => 'right',
        'comment' => 'lang:igniter::admin.help_permalink',
    ],
    'location_email' => [
        'label' => 'lang:igniter::admin.label_email',
        'type' => 'text',
        'span' => 'left',
    ],
    'location_telephone' => [
        'label' => 'lang:igniter.local::default.label_telephone',
        'type' => 'text',
        'span' => 'right',
    ],
    'location_address_1' => [
        'label' => 'lang:igniter.local::default.label_address_1',
        'type' => 'text',
        'span' => 'left',
    ],
    'location_address_2' => [
        'label' => 'lang:igniter.local::default.label_address_2',
        'type' => 'text',
        'span' => 'right',
    ],
    'location_city' => [
        'label' => 'lang:igniter.local::default.label_city',
        'type' => 'text',
        'span' => 'left',
    ],
    'location_state' => [
        'label' => 'lang:igniter.local::default.label_state',
        'type' => 'text',
        'span' => 'right',
    ],
    'location_postcode' => [
        'label' => 'lang:igniter.local::default.label_postcode',
        'type' => 'text',
        'span' => 'left',
    ],
    'thumb' => [
        'label' => 'lang:igniter.local::default.label_image',
        'type' => 'mediafinder',
        'span' => 'left',
        'mode' => 'inline',
        'useAttachment' => true,
        'comment' => 'lang:igniter.local::default.help_image',
    ],
    'options[auto_lat_lng]' => [
        'label' => 'lang:igniter.local::default.label_auto_lat_lng',
        'type' => 'switch',
        'default' => true,
        'onText' => 'lang:igniter::admin.text_yes',
        'offText' => 'lang:igniter::admin.text_no',
        'span' => 'left',
    ],
    'location_status' => [
        'label' => 'lang:igniter::admin.label_status',
        'type' => 'switch',
        'default' => 1,
        'span' => 'right',
        'cssClass' => 'flex-width',
    ],
    'is_default' => [
        'label' => 'lang:igniter::admin.text_is_default',
        'type' => 'switch',
        'default' => 1,
        'span' => 'right',
        'cssClass' => 'flex-width',
    ],
    'location_lat' => [
        'label' => 'lang:igniter.local::default.label_latitude',
        'type' => 'text',
        'span' => 'left',
        'trigger' => [
            'action' => 'hide',
            'field' => 'options[auto_lat_lng]',
            'condition' => 'checked',
        ],
    ],
    'location_lng' => [
        'label' => 'lang:igniter.local::default.label_longitude',
        'type' => 'text',
        'span' => 'right',
        'trigger' => [
            'action' => 'hide',
            'field' => 'options[auto_lat_lng]',
            'condition' => 'checked',
        ],
    ],
    'description' => [
        'label' => 'lang:igniter::admin.label_description',
        'type' => 'richeditor',
        'size' => 'small',
    ],
];

return $config;
