<?php

namespace Igniter\Local\Http\Controllers;

use Igniter\Admin\Classes\ListColumn;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Local\Models\Location;

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
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'locations/edit/{location_id}',
            'redirectClose' => 'locations',
            'redirectNew' => 'locations/create',
            'request' => \Igniter\Local\Http\Requests\LocationRequest::class,
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'locations/edit/{location_id}',
            'redirectClose' => 'locations',
            'redirectNew' => 'locations/create',
            'request' => \Igniter\Local\Http\Requests\LocationRequest::class,
        ],
        'settings' => [
            'title' => 'lang:igniter.local::default.settings_title',
            'redirect' => 'locations/settings/{location_id}',
            'redirectClose' => 'locations',
            'configFile' => 'locationsettings',
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

        AdminMenu::setContext('locations', 'system');
    }

    public function settings(string $context, string $recordId)
    {
        $this->defaultView = 'edit';
        $this->asExtension('FormController')->edit($context, $recordId);
    }

    public function settings_onSave(string $context, string $recordId)
    {
        return $this->asExtension('FormController')->edit_onSave($context, $recordId);
    }

    public function mapViewCenterCoords()
    {
        $model = $this->getFormModel();

        return [
            'lat' => $model->location_lat,
            'lng' => $model->location_lng,
        ];
    }

    public function index_onSetDefault(?string $context)
    {
        $data = $this->validate(post(), [
            'default' => 'required|integer|exists:'.Location::class.',location_id',
        ]);

        if (Location::updateDefault($data['default'])) {
            flash()->success(sprintf(lang('igniter::admin.alert_success'), lang('igniter.local::default.alert_set_default')));
        }

        return $this->refreshList('list');
    }

    public function listOverrideColumnValue(Location $record, ListColumn $column, ?string $alias = null)
    {
        if ($column->type == 'button' && $column->columnName == 'default') {
            $column->iconCssClass = $record->isDefault() ? 'fa fa-star' : 'fa fa-star-o';
        }
    }
}
