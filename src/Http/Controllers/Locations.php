<?php

namespace Igniter\Local\Http\Controllers;

use Exception;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Flame\Geolite\Facades\Geocoder;
use Igniter\Local\Facades\AdminLocation;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationOption;

class Locations extends \Igniter\Admin\Classes\AdminController
{
    public $implement = [
        \Igniter\Admin\Http\Actions\ListController::class,
        \Igniter\Admin\Http\Actions\FormController::class,
    ];

    public $listConfig = [
        'list' => [
            'model' => \Igniter\Local\Models\Location::class,
            'title' => 'lang:igniter.local::default.text_title',
            'emptyMessage' => 'lang:igniter.local::default.text_empty',
            'defaultSort' => ['location_id', 'DESC'],
            'configFile' => 'location',
        ],
    ];

    public $formConfig = [
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

    protected $requiredPermissions = 'Admin.Locations';

    public static function getSlug()
    {
        return 'locations';
    }

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('locations', 'restaurant');
    }

    public function remap(string $action, array $params): mixed
    {
        if ($action != 'settings' && AdminLocation::check()) {
            return $this->redirect('locations/settings');
        }

        return parent::remap($action, $params);
    }

    public function settings($context = null)
    {
        if (!AdminLocation::check()) {
            return $this->redirect('locations');
        }

        $this->asExtension('FormController')->edit('edit', $this->getLocationId());
    }

    public function index_onSetDefault($context = null)
    {
        $defaultId = post('default');

        if (Location::updateDefault($defaultId)) {
            flash()->success(sprintf(lang('igniter::admin.alert_success'), lang('igniter.local::default.alert_set_default')));
        }

        return $this->refreshList('list');
    }

    public function settings_onSave($context = null)
    {
        try {
            $this->asExtension('FormController')->edit_onSave('edit', $this->getLocationId());

            return $this->refresh();
        } catch (Exception $ex) {
            $this->handleError($ex);
        }
    }

    public function listOverrideColumnValue($record, $column, $alias = null)
    {
        if ($column->type != 'button') {
            return null;
        }

        if ($column->columnName != 'default') {
            return null;
        }

        $attributes = $column->attributes;
        $column->iconCssClass = 'fa fa-star-o';
        if ($record->isDefault()) {
            $column->iconCssClass = 'fa fa-star';
        }

        return $attributes;
    }

    public function listExtendQuery($query)
    {
        if (!is_null($ids = AdminLocation::getAll())) {
            $query->whereIn('location_id', $ids);
        }
    }

    public function formExtendQuery($query)
    {
        if (!is_null($ids = AdminLocation::getAll())) {
            $query->whereIn('location_id', $ids);
        }
    }

    public function formExtendFields($form)
    {
        if ($form->model->exists && $form->context != 'create') {
            $form->addTabFields(LocationOption::getFieldsConfig());
        }
    }

    public function getAccordionFields($fields)
    {
        return collect($fields)->mapToGroups(function ($field) {
            $key = array_get($field->config, 'accordion');

            return [$key => $field];
        })->all();
    }

    public function formAfterSave($model)
    {
        if (post('Location.options.auto_lat_lng')) {
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
