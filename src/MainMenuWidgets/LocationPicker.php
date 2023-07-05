<?php

namespace Igniter\Local\MainMenuWidgets;

use Igniter\Admin\Traits\FormModelWidget;
use Igniter\Admin\Traits\ValidatesForm;
use Igniter\Admin\Widgets\Form;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Local\Models\Location;
use Igniter\Local\Requests\LocationRequest;
use Igniter\User\Facades\AdminAuth;
use Illuminate\Support\Facades\DB;

class LocationPicker extends \Igniter\Admin\Classes\BaseMainMenuWidget
{
    use FormModelWidget;
    use ValidatesForm;

    public string|array $form = [];

    public $modelClass = Location::class;

    public function initialize()
    {
        $this->fillFromConfig([
            'form',
            'modelClass',
        ]);
    }

    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('locationpicker/locationpicker');
    }

    public function prepareVars()
    {
        $this->vars['locations'] = $this->listLocations();
        $this->vars['activeLocation'] = LocationFacade::current();
        $this->vars['canCreateLocation'] = AdminAuth::user()->hasPermission('Admin.Locations');
    }

    public function onLoadForm()
    {
        $model = strlen($recordId = post('location', ''))
            ? $this->findFormModel($recordId)
            : $this->createFormModel();

        return $this->makePartial('locationpicker/form', [
            'formRecordId' => $recordId,
            'formTitle' => lang($model->exists ? 'igniter.local::default.picker.text_edit_location' : 'igniter.local::default.picker.text_new_location'),
            'formWidget' => $this->makeLocationFormWidget($model),
        ]);
    }

    public function onChoose()
    {
        throw_unless(
            is_numeric($locationId = post('location')),
            new ApplicationException(lang('igniter.local::default.picker.alert_location_required'))
        );

        throw_unless(
            $location = Location::find($locationId),
            new ApplicationException(lang('igniter.local::default.picker.alert_location_not_found'))
        );

        throw_unless(
            $this->getController()->getUser()->isAssignedLocation($location),
            new ApplicationException(lang('igniter.local::default.picker.alert_location_not_allowed'))
        );

        $currentLocation = LocationFacade::current();
        if ($currentLocation && $currentLocation->getKey() === $location->getKey()) {
            LocationFacade::resetSession();
        } else {
            LocationFacade::setCurrent($location);
        }

        return $this->controller->redirectBack();
    }

    public function onSaveRecord()
    {
        $model = strlen($recordId = post('recordId'))
            ? $this->findFormModel($recordId)
            : $this->createFormModel();

        $form = $this->makeLocationFormWidget($model);

        $this->config['request'] = LocationRequest::class;
        $saveData = $this->validateFormWidget($form, $form->getSaveData());

        $modelsToSave = $this->prepareModelsToSave($model, $saveData);

        DB::transaction(function () use ($modelsToSave) {
            foreach ($modelsToSave as $modelToSave) {
                $modelToSave->saveOrFail();
            }
        });

        flash()->success(sprintf(lang('igniter::admin.alert_success'),
            lang('igniter.local::default.picker.text_form_name').' '.($form->context == 'create' ? 'created' : 'updated')))->now();

        return $this->reload();
    }

    public function onDeleteRecord()
    {
        $model = $this->findFormModel(post('recordId'));

        $form = $this->makeLocationFormWidget($model);

        $model->delete();

        flash()->success(sprintf(lang('igniter::admin.alert_success'), lang($this->formName).' deleted'))->now();

        return $this->reload();
    }

    protected function makeLocationFormWidget($model)
    {
        $context = $model->exists ? 'edit' : 'create';

        $formConfig = is_string($this->form) ? $this->loadConfig($this->form, ['form'], 'form') : $this->form;
        $widgetConfig = array_except($formConfig, 'toolbar', []);
        $widgetConfig['model'] = $model;
        $widgetConfig['alias'] = $this->alias.'RecordEditor';
        $widgetConfig['arrayName'] = 'LocationData';
        $widgetConfig['context'] = $context;
        $widget = $this->makeWidget(Form::class, $widgetConfig);

        $widget->bindToController();

        return $widget;
    }

    protected function listLocations()
    {
        return $this->getController()->getUser()->getAvailableLocations();
    }
}