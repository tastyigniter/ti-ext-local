<?php

return [
    'columns' => [
        'menu_id' => 'lang:admin::lang.column_id',
        'menu_name' => 'lang:admin::lang.label_name',
        'menu_price' => 'lang:admin::lang.menus.label_price',
        'menu_description' => 'lang:admin::lang.label_description',
        'minimum_qty' => 'lang:admin::lang.menus.label_minimum_qty',
        'subtract_stock' => 'lang:admin::lang.menus.label_subtract_stock',
        'stock_qty' => 'lang:admin::lang.menus.label_stock_qty',
        'categories' => 'lang:admin::lang.menus.label_category',
        'menu_status' => 'lang:admin::lang.label_status',
        'mealtime' => 'lang:admin::lang.menus.label_mealtime',
    ],
    'fields' => [
        'update_existing' => [
            'label' => 'Update existing menu items',
            'type' => 'switch',
            'default' => TRUE,
        ],
    ],
];