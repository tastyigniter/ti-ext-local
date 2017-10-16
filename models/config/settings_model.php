<?php

return [
    'form' => [
        'toolbar' => [
            'buttons' => [
                'save'      => ['label' => 'lang:admin::default.button_save', 'class' => 'btn btn-primary', 'data-request' => 'onSave'],
                'saveClose' => [
                    'label'             => 'lang:admin::default.button_save_close',
                    'class'             => 'btn btn-default',
                    'data-request'      => 'onSave',
                    'data-request-data' => 'close:1',
                ],
                'back'      => ['label' => 'lang:admin::default.button_icon_back', 'class' => 'btn btn-default', 'href' => 'settings'],
            ],
        ],
        'fields'  => [
            'location_search_mode' => [
                'label' => 'lang:sampoyigi.local::default.label_location_search_mode',
                'type'  => 'switch',
            ],
            'use_location'         => [
                'label' => 'lang:sampoyigi.local::default.label_use_location',
                'type'  => 'switch',
            ],
            'status'               => [
                'label' => 'lang:sampoyigi.local::default.label_status',
                'type'  => 'switch',
            ],
        ],
        'rules'   => [
            ['location_search_mode', 'lang:sampoyigi.local::default.label_location_search_mode', 'required|integer'],
            ['use_location', 'lang:sampoyigi.local::default.label_use_location', 'required|integer'],
            ['status', 'lang:sampoyigi.local::default.label_status', 'required|integer'],
        ],
    ],
];