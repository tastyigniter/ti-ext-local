<?php

namespace Igniter\Local\Http\Controllers;

use Igniter\Admin\Facades\AdminMenu;

class Locations extends \Igniter\Admin\Classes\AdminController
{
    public array $implement = [
        \Igniter\Admin\Http\Actions\ListController::class,
        \Igniter\Admin\Http\Actions\FormController::class,
    ];

    public array $listConfig = [
        'list' => [
            'model' => \Igniter\Local\Models\Location::class,
            'title' => 'lang:igniter.local::default.text_title',
            'emptyMessage' => 'lang:igniter.local::default.text_empty',
            'defaultSort' => ['location_id', 'DESC'],
            'configFile' => 'location',
            'back' => 'settings',
        ],
    ];

    public array $formConfig = [
        'name' => 'lang:igniter.local::default.text_form_name',
        'model' => \Igniter\Local\Models\Location::class,
        'request' => \Igniter\Local\Requests\LocationRequest::class,
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'locations/edit/{location_id}',
            'redirectClose' => 'locations',
            'redirectNew' => 'locations/create',
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'locations/edit/{location_id}',
            'redirectClose' => 'locations',
            'redirectNew' => 'locations/create',
        ],
        'preview' => [
            'title' => 'lang:igniter::admin.form.preview_title',
            'redirect' => 'locations',
        ],
        'delete' => [
            'redirect' => 'locations',
        ],
        'configFile' => 'location',
    ];

    protected null|string|array $requiredPermissions = 'Admin.Locations';

    public static function getSlug()
    {
        return 'locations';
    }

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('settings', 'system');
    }

    public function mapViewCenterCoords()
    {
        $model = $this->getFormModel();

        return [
            'lat' => $model->location_lat,
            'lng' => $model->location_lng,
        ];
    }
}
