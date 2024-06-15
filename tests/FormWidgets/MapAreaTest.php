<?php

namespace Igniter\Local\Tests\FormWidgets;

use Igniter\Admin\Classes\FormField;
use Igniter\Local\FormWidgets\MapArea;
use Igniter\Local\Http\Controllers\Locations;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationArea;
use Igniter\System\Facades\Assets;
use Igniter\System\Models\Settings;

beforeEach(function() {
    $this->location = Location::factory()->create();
    $formField = (new FormField('test_field', 'Map area'))
        ->displayAs('maparea', ['valueFrom' => 'delivery_areas']);
    $this->mapAreaWidget = new MapArea(resolve(Locations::class), $formField, [
        'model' => $this->location,
    ]);
});

it('initializes correctly', function() {
    expect($this->mapAreaWidget->modelClass)->toBe(LocationArea::class)
        ->and($this->mapAreaWidget->prompt)->toBe('lang:igniter.local::default.text_add_new_area')
        ->and($this->mapAreaWidget->formName)->toBe('lang:igniter.local::default.text_edit_area')
        ->and($this->mapAreaWidget->addLabel)->toBe('New')
        ->and($this->mapAreaWidget->editLabel)->toBe('Edit')
        ->and($this->mapAreaWidget->deleteLabel)->toBe('Delete')
        ->and($this->mapAreaWidget->sortColumnName)->toBe('priority')
        ->and($this->mapAreaWidget->sortable)->toBeTrue();
});

it('loads assets correctly', function() {
    Settings::set('maps_api_key', 'test_key');

    Assets::shouldReceive('addCss')->once()->with('maparea.css', 'maparea-css');
    Assets::shouldReceive('addJs')->once()->with('formwidgets/repeater.js', 'repeater-js');
    Assets::shouldReceive('addJs')->once()->with('formwidgets/recordeditor.modal.js', 'recordeditor-modal-js');
    Assets::shouldReceive('addJs')->once()->with('maparea.js', 'maparea-js');
    Assets::shouldReceive('addJs')->once()->with('mapview.js', 'mapview-js');
    Assets::shouldReceive('addJs')->once()->with('mapview.shape.js', 'mapview-shape-js');
    Assets::shouldReceive('addJs')->once()->withArgs(function($url, $name) {
        return str_contains($url, 'googleapis.com/maps/api/js');
    });

    $this->mapAreaWidget->assetPath = [];

    $this->mapAreaWidget->loadAssets();
});

it('prepares variables correctly', function() {
    $this->mapAreaWidget->prepareVars();

    expect($this->mapAreaWidget->vars['field'])->toBeInstanceOf(FormField::class)
        ->and($this->mapAreaWidget->vars['mapAreas'])->toBeArray()
        ->and($this->mapAreaWidget->vars['sortable'])->toBeBool()
        ->and($this->mapAreaWidget->vars['sortableInputName'])->toBe(MapArea::SORT_PREFIX.'test_field')
        ->and($this->mapAreaWidget->vars)->toHaveKey('prompt');
});

it('saves value correctly', function() {
    expect($this->mapAreaWidget->getSaveValue([]))->toBeNull();
});

it('returns no save value when sortable is disabled correctly', function() {
    $this->mapAreaWidget->sortable = false;

    expect($this->mapAreaWidget->getSaveValue([]))->toBe(FormField::NO_SAVE_DATA);
});

it('loads record correctly', function() {
    expect($this->mapAreaWidget->onLoadRecord())->toBeString();
});

it('saves record correctly', function() {
    expect($this->mapAreaWidget->onSaveRecord())->toBeArray();
});

it('deletes area correctly', function() {
    $locationArea = LocationArea::factory()->create();
    request()->merge(['areaId' => $locationArea->area_id]);

    $this->mapAreaWidget->onDeleteArea();

    expect(LocationArea::find($locationArea->area_id))->toBeNull();
});

it('gets map shape attributes correctly', function() {
    expect($this->mapAreaWidget->getMapShapeAttributes(new LocationArea()))->toBeString();
});
