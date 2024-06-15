<?php

namespace Igniter\Local\Tests\FormWidgets;

use Igniter\Admin\Classes\FormField;
use Igniter\Admin\Http\Actions\FormController;
use Igniter\Local\FormWidgets\SettingsEditor;
use Igniter\Local\Http\Controllers\Locations;
use Igniter\Local\Models\Location;
use Igniter\System\Facades\Assets;

beforeEach(function() {
    $this->location = Location::factory()->create();

    $this->controller = resolve(Locations::class);
    $this->controller->asExtension(FormController::class)->initForm($this->location);

    $formField = (new FormField('test_field', 'Settings editor'))->displayAs('settingseditor');
    $this->settingsEditorWidget = new SettingsEditor($this->controller, $formField, [
        'model' => $this->location,
    ]);
});

it('initializes correctly', function() {
    $this->settingsEditorWidget->initialize();

    expect($this->settingsEditorWidget->popupSize)->toBe('modal-lg');
});

it('prepares variables correctly', function() {
    $this->settingsEditorWidget->prepareVars();

    expect($this->settingsEditorWidget->vars['field'])->toBeInstanceOf(FormField::class)
        ->and($this->settingsEditorWidget->vars['settings'])->toBeArray();
});

it('loads assets correctly', function() {
    Assets::shouldReceive('addJs')->once()->with('formwidgets/recordeditor.modal.js', 'recordeditor-modal-js');

    $this->settingsEditorWidget->assetPath = [];

    $this->settingsEditorWidget->loadAssets();
});

it('loads record correctly', function() {
    request()->merge(['code' => 'checkout']);
    expect($this->settingsEditorWidget->onLoadRecord())->toBeString();
});

it('saves record correctly', function() {
    request()->merge(['recordId' => 'checkout']);
    expect($this->settingsEditorWidget->onSaveRecord())->toBeNull();
});
