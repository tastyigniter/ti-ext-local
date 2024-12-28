<?php

namespace Igniter\Local\Tests\Models\Concerns;

use Igniter\Flame\Database\Attach\Media;
use Igniter\Flame\Geolite\Contracts\CoordinatesInterface;
use Igniter\Local\Models\Location;
use Igniter\System\Models\Country;

it('returns correct location name', function() {
    $location = Location::factory()->create(['location_name' => 'Test Location']);

    $result = $location->getName();

    expect($result)->toBe('Test Location');
});

it('returns email in lowercase', function() {
    $location = Location::factory()->create(['location_email' => 'TEST@EXAMPLE.COM']);

    $result = $location->getEmail();

    expect($result)->toBe('test@example.com');
});

it('returns correct telephone number', function() {
    $location = Location::factory()->create(['location_telephone' => '123456789']);

    $result = $location->getTelephone();

    expect($result)->toBe('123456789');
});

it('returns correct description', function() {
    $location = Location::factory()->create(['description' => 'Test Description']);

    $result = $location->getDescription();

    expect($result)->toBe('Test Description');
});

it('returns correct address', function() {
    $country = Country::factory()->create([
        'country_name' => 'Test Country',
        'iso_code_2' => 'TC',
        'iso_code_3' => 'TST',
        'format' => 'Test Format',
    ]);
    $location = Location::factory()->create([
        'location_address_1' => 'Address 1',
        'location_address_2' => 'Address 2',
        'location_city' => 'City',
        'location_state' => 'State',
        'location_postcode' => '12345',
        'location_lat' => 12.345678,
        'location_lng' => 98.765432,
        'location_country_id' => $country->getKey(),
    ]);

    $result = $location->getAddress();

    expect($result)->toBe([
        'address_1' => 'Address 1',
        'address_2' => 'Address 2',
        'city' => 'City',
        'state' => 'State',
        'postcode' => '12345',
        'location_lat' => 12.345678,
        'location_lng' => 98.765432,
        'country_id' => $country->getKey(),
        'country' => 'Test Country',
        'iso_code_2' => 'TC',
        'iso_code_3' => 'TST',
        'format' => 'Test Format',
    ]);
});

it('calculates correct distance', function() {
    $location = Location::factory()->create(['location_lat' => '12.345678', 'location_lng' => '98.765432']);
    $position = mock(CoordinatesInterface::class);
    $position->shouldReceive('getLatitude')->andReturn('12.345678');
    $position->shouldReceive('getLongitude')->andReturn('98.765432');

    $result = $location->calculateDistance($position);

    expect($result->getDistance())->toBe(0.0);
});

it('returns correct coordinates', function() {
    $location = Location::factory()->create([
        'location_lat' => '12.345678',
        'location_lng' => '98.765432',
    ]);

    $result = $location->getCoordinates();

    expect($result->getLatitude())->toBe(12.345678)
        ->and($result->getLongitude())->toBe(98.765432);
});

it('sets correct URL with suffix', function() {
    $location = Location::factory()->create(['permalink_slug' => 'test-location']);

    $location->setUrl('/test-suffix');

    expect($location->url)->toBe(page_url('test-location/test-suffix'));
});

it('sets correct URL without suffix for single location', function() {
    $location = Location::factory()->create(['permalink_slug' => 'test-location']);
    config(['igniter-system.locationMode' => 'single']);

    $location->setUrl();

    expect($location->url)->toBe(page_url('test-location/menus'));
});

it('checks if location has gallery', function() {
    $location = Location::factory()->create();
    $media = new Media();
    $media->setRelation('attachment', $location);
    $media->addFromRaw('raw-content', 'media-file.jpg', 'gallery');

    $result = $location->hasGallery();

    expect($result)->toBeTrue();
});

it('returns correct gallery', function() {
    $location = Location::factory()->create();
    $media = Media::create();
    $media->setRelation('attachment', $location);
    $media->addFromRaw('raw-content', 'media-file.jpg', 'gallery');

    $result = $location->getGallery();

    expect($result->first()->id)->toBe($media->id);
});

it('returns correct settings', function() {
    $location = Location::factory()->create();
    $location->settings()->create(['item' => 'test_item', 'data' => 'test_data']);

    $result = $location->getSettings('test_item');

    expect($result)->toBe('test_data');
});

it('finds or creates settings', function() {
    $location = Location::factory()->create();

    $result = $location->findSettings('test_item');

    expect($result->item)->toBe('test_item');
});
