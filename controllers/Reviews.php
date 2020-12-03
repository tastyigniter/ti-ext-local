<?php

namespace Igniter\Local\Controllers;

use AdminMenu;

class Reviews extends \Admin\Classes\AdminController
{
    public $implement = [
        'Admin\Actions\ListController',
        'Admin\Actions\FormController',
        'Admin\Actions\LocationAwareController',
    ];

    public $listConfig = [
        'list' => [
            'model' => 'Igniter\Local\Models\Reviews_model',
            'title' => 'lang:igniter.local::default.reviews.text_title',
            'emptyMessage' => 'lang:igniter.local::default.reviews.text_empty',
            'defaultSort' => ['review_id', 'DESC'],
            'configFile' => 'reviews_model',
        ],
    ];

    public $formConfig = [
        'name' => 'lang:igniter.local::default.reviews.text_form_name',
        'model' => 'Igniter\Local\Models\Reviews_model',
        'request' => 'Igniter\Local\Requests\Review',
        'create' => [
            'title' => 'lang:admin::lang.form.create_title',
            'redirect' => 'igniter/local/reviews/edit/{review_id}',
            'redirectClose' => 'igniter/local/reviews',
        ],
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'igniter/local/reviews/edit/{review_id}',
            'redirectClose' => 'igniter/local/reviews',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'redirect' => 'igniter/local/reviews',
        ],
        'delete' => [
            'redirect' => 'igniter/local/reviews',
        ],
        'configFile' => 'reviews_model',
    ];

    protected $requiredPermissions = 'Admin.Reviews';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('reviews', 'sales');
    }
}
