<?php

namespace Igniter\Local\FormWidgets;

use Igniter\Admin\Classes\BaseFormWidget;
use Igniter\Admin\Traits\ValidatesForm;
use Igniter\Admin\Widgets\Form;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Models\LocationSettings;
use Illuminate\Support\Facades\DB;

class SettingsEditor extends BaseFormWidget
{
    use ValidatesForm;

    /**
     * @var \Igniter\Local\Models\Location Form model object.
     */
    public ?Model $model = null;

    public $form;

    public $popupSize = 'modal-lg';

    public function initialize()
    {
        $this->fillFromConfig([
            'form',
        ]);
    }

    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('settingseditor/settingseditor');
    }

    public function prepareVars()
    {
        $this->vars['field'] = $this->formField;
        $this->vars['settings'] = $this->listSettings();
    }

    public function loadAssets()
    {
        $this->addJs('formwidgets/recordeditor.modal.js', 'recordeditor-modal-js');
    }

    public function onLoadRecord()
    {
        throw_unless($settingsCode = input('code'), new ApplicationException('Missing settings code'));

        $definition = $this->getSettings($settingsCode);

        $model = LocationSettings::instance($this->model, $definition->code);

        return $this->makePartial('recordeditor/form', [
            'formRecordId' => $settingsCode,
            'formTitle' => lang($definition->label),
            'formWidget' => $this->makeSettingsFormWidget($model, $definition),
        ]);
    }

    public function onSaveRecord()
    {
        throw_unless($settingsCode = input('recordId'), new ApplicationException('Missing settings code'));

        $definition = $this->getSettings($settingsCode);

        $model = LocationSettings::instance($this->model, $definition->code);

        $form = $this->makeSettingsFormWidget($model, $definition);

        $saveData = $this->validateFormWidget($form, $form->getSaveData());

        DB::transaction(function() use ($model, $saveData) {
            $model->fill($saveData)->save();
        });

        flash()->success(sprintf(lang('igniter::admin.alert_success'), lang($definition->label).' '.'updated'))->now();
    }

    protected function getSettings($settingsCode)
    {
        throw_unless(
            $definition = array_get($this->listSettings(), $settingsCode),
            new ApplicationException(lang('igniter.local::default.alert_settings_not_loaded'))
        );

        return $definition;
    }

    protected function listSettings()
    {
        return LocationSettings::make()->listRegisteredSettings();
    }

    protected function makeSettingsFormWidget($model, $definition)
    {
        $widgetConfig = is_string($definition->form) ? $this->loadConfig($definition->form, ['form'], 'form') : $this->form;
        $widgetConfig['model'] = $model;
        $widgetConfig['data'] = $model;
        $widgetConfig['alias'] = $this->alias.'FormSettingsEditor';
        $widgetConfig['arrayName'] = $this->formField->arrayName.'[settingsData]';
        $widgetConfig['context'] = 'edit';
        $widget = $this->makeWidget(Form::class, $widgetConfig);

        $widget->bindToController();
        $widget->previewMode = $this->previewMode;

        return $widget;
    }
}
