<?php

namespace Igniter\Local\Tests\FormWidgets;

use Igniter\Admin\Classes\FormField;
use Igniter\Admin\Http\Actions\FormController;
use Igniter\Local\FormWidgets\MapView;
use Igniter\Local\Http\Controllers\Locations;
use Igniter\Local\Models\Location;
use Igniter\System\Facades\Assets;
use Igniter\System\Models\Settings;

beforeEach(function() {
    $this->location = Location::factory()->create();

    $this->controller = resolve(Locations::class);
    $this->controller->asExtension(FormController::class)->initForm($this->location);

    $formField = (new FormField('test_field', 'Map view'))->displayAs('mapview');
    $this->mapViewWidget = new MapView($this->controller, $formField, [
        'model' => $this->location,
    ]);
});

it('initializes correctly', function() {
    $this->mapViewWidget->initialize();

    expect($this->mapViewWidget->height)->toBe(500)
        ->and($this->mapViewWidget->zoom)->toBeNull()
        ->and($this->mapViewWidget->center)->toBeNull()
        ->and($this->mapViewWidget->shapeSelector)->toBe('[data-map-shape]');
});

it('loads assets correctly', function() {
    Settings::set('maps_api_key', 'test_key');

    Assets::shouldReceive('addJs')->once()->with('mapview.js', 'mapview-js');
    Assets::shouldReceive('addJs')->once()->with('mapview.shape.js', 'mapview-shape-js');
    Assets::shouldReceive('addJs')->once()->withArgs(function($url, $name) {
        return str_contains($url, 'googleapis.com/maps/api/js');
    });

    $this->mapViewWidget->assetPath = [];

    $this->mapViewWidget->loadAssets();
});

it('prepares variables correctly', function() {
    $this->mapViewWidget->prepareVars();

    expect($this->mapViewWidget->vars['mapHeight'])->toBe(500)
        ->and($this->mapViewWidget->vars['mapZoom'])->toBe(0)
        ->and($this->mapViewWidget->vars['mapCenter'])->toBeArray()
        ->and($this->mapViewWidget->vars['shapeSelector'])->toBe('[data-map-shape]')
        ->and($this->mapViewWidget->vars['previewMode'])->toBeFalse();
});

it('checks configuration correctly', function() {
    Settings::set('maps_api_key', 'test_key');

    expect($this->mapViewWidget->isConfigured())->toBeTrue();

    Settings::set('maps_api_key', '');

    expect($this->mapViewWidget->isConfigured())->toBeFalse();
});

it('returns center correctly', function() {
    expect($this->mapViewWidget->hasCenter())->toBeTrue();
});
