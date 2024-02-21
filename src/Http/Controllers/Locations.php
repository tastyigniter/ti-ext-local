<?php

namespace Igniter\Local\Http\Controllers;

use Igniter\Admin\Facades\AdminMenu;
use Igniter\Local\Facades\Location as LocationFacade;

class Locations extends \Igniter\Admin\Classes\AdminController
{
    public array $implement = [
        \Igniter\Admin\Http\Actions\FormController::class,
        \Igniter\Local\Http\Actions\LocationAwareController::class,
    ];

    public array $formConfig = [
        'name' => 'lang:igniter.local::default.text_form_name',
        'model' => \Igniter\Local\Models\Location::class,
        'settings' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'locations/settings',
        ],
        'configFile' => 'locationsettings',
    ];

    protected null|string|array $requiredPermissions = 'Admin.Locations';

    public static function getSlug()
    {
        return 'locations';
    }

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('locationsettings', 'restaurant');
    }

    public function settings($context = null)
    {
        if (!LocationFacade::check()) {
            return $this->makeView('select_location');
        }

        $this->defaultView = 'edit';

        $this->asExtension('FormController')->edit($context, LocationFacade::getId());
    }

    public function settings_onSave($context = null)
    {
        return $this->asExtension('FormController')->edit_onSave($context, LocationFacade::getId());
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
