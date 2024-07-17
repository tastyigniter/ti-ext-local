<?php

namespace Igniter\Local\MainMenuWidgets;

use Igniter\Admin\Traits\FormModelWidget;
use Igniter\Admin\Traits\ValidatesForm;
use Igniter\Admin\Widgets\Form;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Flame\Exception\FlashException;
use Igniter\Flame\Geolite\Facades\Geocoder;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Local\Models\Location;
use Igniter\User\Facades\AdminAuth;
use Illuminate\Support\Facades\DB;

class LocationPicker extends \Igniter\Admin\Classes\BaseMainMenuWidget
{
    use FormModelWidget;
    use ValidatesForm;

    public string $popupSize = 'modal-lg';

    public string|array $form = [];

    public $modelClass = Location::class;

    public function initialize()
    {
        $this->fillFromConfig([
            'form',
            'popupSize',
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
        $location = LocationFacade::current();
        $this->vars['locations'] = $this->listLocations();
        $this->vars['activeLocation'] = $location && AdminAuth::user()->isAssignedLocation($location) ? $location : null;
        $this->vars['canCreateLocation'] = AdminAuth::user()->hasPermission('Admin.Locations');
        $this->vars['isSingleMode'] = is_single_location();
    }

    public function onLoadForm()
    {
        $model = strlen($recordId = post('location', ''))
            ? $this->findFormModel($recordId)
            : $this->createFormModel();

        return $this->makePartial('formwidgets/recordeditor/form', [
            'formRecordId' => $recordId,
            'showDeleteButton' => $model->exists,
            'formTitle' => lang($model->exists ? 'igniter.local::default.picker.text_edit_location' : 'igniter.local::default.picker.text_new_location'),
            'formWidget' => $this->makeLocationFormWidget($model),
        ]);
    }

    public function onChoose()
    {
        throw_unless(
            is_numeric($locationId = input('location')),
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
        throw_unless($this->getController()->authorize('Admin.Locations'),
            new FlashException(lang('igniter.local::default.picker.alert_user_restricted'))
        );

        $model = strlen($recordId = post('recordId'))
            ? $this->findFormModel($recordId)
            : $this->createFormModel();

        $form = $this->makeLocationFormWidget($model);

        $saveData = $this->validateFormWidget($form, $form->getSaveData());

        $modelsToSave = $this->prepareModelsToSave($model, $saveData);

        DB::transaction(function() use ($modelsToSave) {
            foreach ($modelsToSave as $modelToSave) {
                $modelToSave->saveOrFail();
            }
        });

        if ($logs = Geocoder::getLogs()) {
            flash()->error(implode(PHP_EOL, $logs))->important()->now();
        }

        flash()->success(sprintf(lang('igniter::admin.alert_success'),
            lang('igniter.local::default.picker.text_form_name').' '.($form->context == 'create' ? 'created' : 'updated')))->now();

        return $this->reload();
    }

    public function onDeleteRecord()
    {
        throw_unless($this->getController()->authorize('Admin.Locations'),
            new FlashException(lang('igniter.local::default.picker.alert_user_restricted'))
        );

        $model = $this->findFormModel((string)input('recordId'));

        $model->delete();

        flash()->success(sprintf(lang('igniter::admin.alert_success'), lang('igniter.local::default.picker.text_form_name').' deleted'))->now();

        return $this->reload();
    }

    protected function makeLocationFormWidget($model)
    {
        $context = $model->exists ? 'edit' : 'create';
        if (!AdminAuth::user()->hasPermission('Admin.Locations')) {
            $context = 'preview';
        }

        $formConfig = is_string($this->form) ? $this->loadConfig($this->form, ['form'], 'form') : $this->form;
        $widgetConfig = array_except($formConfig, 'toolbar', []);
        $widgetConfig['model'] = $model;
        $widgetConfig['alias'] = $this->alias.'RecordEditor';
        $widgetConfig['arrayName'] = 'LocationData';
        $widgetConfig['context'] = $context;
        $widget = $this->makeWidget(Form::class, $widgetConfig);

        if ($context === 'preview') {
            $widget->previewMode = true;
        }

        $widget->bindToController();

        return $widget;
    }

    protected function listLocations()
    {
        return $this->getController()->getUser()->getAvailableLocations();
    }
}
