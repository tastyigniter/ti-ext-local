<?php

declare(strict_types=1);

namespace Igniter\Local\Tests\Models;

use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationSettings;

beforeEach(function(): void {
    LocationSettings::clearInternalCache();
});

it('creates a new instance with given location and settings code', function(): void {
    $settingsCode = 'test_code';
    $location = Location::factory()->create();
    $location->settings()->create(['item' => $settingsCode]);

    $result = LocationSettings::instance($location, $settingsCode);

    expect($result)->toBeInstanceOf(LocationSettings::class)
        ->and($result->location_id)->toBe($location->getKey())
        ->and($result->item)->toBe($settingsCode);
});

it('returns existing instance from cache', function(): void {
    $settingsCode = 'test_code';
    $location = Location::factory()->create();
    $location->settings()->create(['item' => $settingsCode]);
    $instance = LocationSettings::instance($location, $settingsCode);

    $result = LocationSettings::instance($location, $settingsCode);

    expect($result)->toBe($instance);
});

it('sets settings value for allowed key', function(): void {
    $locationSettings = new LocationSettings();
    $locationSettings->setSettingsValue('allowed_key', 'value');

    $result = $locationSettings->getSettingsValue();

    expect($result['allowed_key'])->toBe('value');
});

it('does not set settings value for disallowed key', function(): void {
    $locationSettings = new LocationSettings();
    $locationSettings->setSettingsValue('id', 'value');

    $result = $locationSettings->getSettingsValue();

    expect($result)->not->toHaveKey('id');
});

it('returns default value if key is not set', function(): void {
    $locationSettings = new LocationSettings();

    $result = $locationSettings->get('non_existent_key', 'default_value');

    expect($result)->toBe('default_value');
});

it('sets settings values after fetching data', function(): void {
    $locationSettings = new class(['data' => ['key' => 'value']]) extends LocationSettings
    {
        public function testAfterFetch(): void
        {
            parent::afterFetch();
        }
    };
    $locationSettings->testAfterFetch();

    expect($locationSettings->getSettingsValue())->toBeArray()
        ->toHaveKey('key', 'value');
});

it('merges settings values with attributes after fetching data', function(): void {
    $locationSettings = new class(['data' => ['key' => 'value'], 'attribute' => 'attr_value']) extends LocationSettings
    {
        public function testAfterFetch(): void
        {
            parent::afterFetch();
        }
    };
    $locationSettings->testAfterFetch();

    expect($locationSettings->getAttributes())
        ->toHaveKey('key', 'value')
        ->toHaveKey('attribute', 'attr_value');
});

it('sets data attribute before saving if settings values are present', function(): void {
    $locationSettings = new class extends LocationSettings
    {
        public function testBeforeSave(): void
        {
            parent::beforeSave();
        }
    };
    $locationSettings->setSettingsValue('key', 'value');
    $locationSettings->testBeforeSave();

    $result = $locationSettings->data;

    expect($result)->toBeArray()
        ->and($result['key'])->toBe('value');
});

it('does not set data attribute before saving if settings values are empty', function(): void {
    $locationSettings = new class extends LocationSettings
    {
        public function testBeforeSave(): void
        {
            parent::beforeSave();
        }
    };
    $locationSettings->testBeforeSave();

    $result = $locationSettings->data;

    expect($result)->toBeNull();
});

it('clears internal cache', function(): void {
    $location = Location::factory()->create();
    $settingsCode = 'test_code';
    LocationSettings::instance($location, $settingsCode);

    LocationSettings::clearInternalCache();

    $result = LocationSettings::instance($location, $settingsCode);

    expect($result)->not->toBeNull();
});

it('loads registered settings', function(): void {
    $locationSettings = new LocationSettings();
    LocationSettings::registerCallback(function($settings): void {
        $settings->registerSettingItems('test_extension', [
            'test_code' => [
                'label' => 'Test Label',
                'form' => 'test_config',
            ],
        ]);
    });

    $locationSettings->loadRegisteredSettings();

    $result = $locationSettings->listRegisteredSettings();

    expect($result)->toHaveKey('test_code')
        ->and($result['test_code']->label)->toBe('Test Label')
        ->and($result['test_code']->form)->toBe('test_extension::test_config');
});

it('configures location settings model correctly', function(): void {
    $locationSettings = new LocationSettings;

    expect($locationSettings->getTable())->toBe('location_settings')
        ->and($locationSettings->timestamps)->toBeFalse()
        ->and($locationSettings->getCasts())->toEqual([
            'id' => 'int',
            'data' => 'array',
        ]);
});
