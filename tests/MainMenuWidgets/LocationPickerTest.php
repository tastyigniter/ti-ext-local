<?php

namespace Igniter\Local\Tests\MainMenuWidgets;

use Igniter\Admin\Classes\MainMenuItem;
use Igniter\Local\Http\Controllers\Locations;
use Igniter\Local\MainMenuWidgets\LocationPicker;
use Igniter\Local\Models\Location;
use Igniter\User\Facades\AdminAuth;
use Igniter\User\Models\User;
use Illuminate\Http\RedirectResponse;

beforeEach(function() {
    $this->location = Location::factory()->create();
    $menuItem = (new MainMenuItem('test_field', 'Location picker'))->displayAs('locationpicker');
    $this->locationPickerWidget = new LocationPicker($controller = resolve(Locations::class), $menuItem);
    $controller->setUser($user = User::factory()->superUser()->create());
    AdminAuth::shouldReceive('user')->andReturn($user);
});

it('initializes correctly', function() {
    $this->locationPickerWidget->initialize();

    expect($this->locationPickerWidget->popupSize)->toBe('modal-lg')
        ->and($this->locationPickerWidget->modelClass)->toBe(Location::class);
});

it('prepares variables correctly', function() {
    $this->locationPickerWidget->prepareVars();

    expect($this->locationPickerWidget->vars)->toBeArray()
        ->toHaveKey('locations')
        ->toHaveKey('activeLocation')
        ->toHaveKey('canCreateLocation')
        ->toHaveKey('isSingleMode');
});

it('loads form correctly', function() {
    expect($this->locationPickerWidget->onLoadForm())->toBeString();
});

it('chooses location correctly', function() {
    $this->actingAs($this->locationPickerWidget->getController()->getUser(), 'igniter-admin');
    request()->merge(['location' => $this->location->getKey()]);

    expect($this->locationPickerWidget->onChoose())->toBeInstanceOf(RedirectResponse::class);
});

it('saves record correctly', function() {
    $this->actingAs($this->locationPickerWidget->getController()->getUser(), 'igniter-admin');

    expect($this->locationPickerWidget->onSaveRecord())->toBeArray();
});

it('deletes record correctly', function() {
    $this->actingAs($this->locationPickerWidget->getController()->getUser(), 'igniter-admin');
    request()->merge(['recordId' => $this->location->getKey()]);

    expect($this->locationPickerWidget->onDeleteRecord())->toBeArray();
});
