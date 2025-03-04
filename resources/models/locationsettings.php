<?php

$config['form']['toolbar'] = [
    'buttons' => [
        'save' => [
            'label' => 'lang:igniter::admin.button_save',
            'class' => 'btn btn-primary',
            'data-request' => 'onSave',
            'data-progress-indicator' => 'igniter::admin.text_saving',
        ],
    ],
];

$config['form']['tabs'] = [
    'defaultTab' => 'lang:igniter.local::default.text_tab_general',
    'fields' => [
        '_general' => [
            'tab' => 'lang:igniter.local::default.text_tab_general',
            'type' => 'settingseditor',
        ],
        '_working_hours' => [
            'tab' => 'lang:igniter.local::default.text_tab_schedules',
            'type' => 'scheduleeditor',
            'form' => 'workinghour',
            'request' => \Igniter\Local\Http\Requests\WorkingHourRequest::class,
        ],

        'delivery_areas' => [
            'tab' => 'lang:igniter.local::default.text_tab_delivery',
            'label' => 'lang:igniter.local::default.text_delivery_area',
            'type' => 'maparea',
            'form' => 'locationarea',
            'request' => \Igniter\Local\Http\Requests\LocationAreaRequest::class,
            'commentAbove' => 'lang:igniter.local::default.help_delivery_areas',
        ],

        'gallery' => [
            'label' => 'lang:igniter.local::default.label_gallery_add_image',
            'tab' => 'lang:igniter.local::default.text_tab_gallery',
            'type' => 'mediafinder',
            'isMulti' => true,
            'useAttachment' => true,
        ],
    ],
];

return $config;
