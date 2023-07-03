<?php

namespace Igniter\Local\Http\Controllers;

use Igniter\Admin\Facades\AdminMenu;
use Igniter\Admin\Widgets\Form;
use Igniter\Flame\Geolite\Facades\Geocoder;
use Igniter\Local\Facades\AdminLocation;
use Illuminate\Support\Facades\Event;

class Locations extends \Igniter\Admin\Classes\AdminController
{
    public $implement = [
        \Igniter\Admin\Http\Actions\FormController::class,
        \Igniter\Local\Http\Actions\LocationAwareController::class,
    ];

    public $formConfig = [
        'name' => 'lang:igniter.local::default.text_form_name',
        'model' => \Igniter\Local\Models\Location::class,
        'settings' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'locations/settings',
        ],
        'configFile' => 'locationsettings',
    ];

    protected $requiredPermissions = 'Admin.Locations';

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
        if (!AdminLocation::check()) {
            return $this->makeView('igniter.local::404');
        }

        $this->defaultView = 'edit';

        Event::listen('admin.form.extendFieldsBefore', function (Form $widget) {
            if ($widget->alias !== 'form') {
                return;
            }

            $widget->tabs['fields']['_more'] = [
                'tab' => 'lang:igniter.local::default.text_tab_more',
                'type' => 'settingseditor',
            ];
        });

        $this->asExtension('FormController')->edit($context, $this->getLocationId());
    }

    public function settings_onSave($context = null)
    {
        return $this->asExtension('FormController')->edit_onSave($context, $this->getLocationId());
    }

    public function formAfterSave($model)
    {
        if ($model->is_auto_lat_lng) {
            if ($logs = Geocoder::getLogs()) {
                flash()->error(implode(PHP_EOL, $logs))->important();
            }
        }
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
