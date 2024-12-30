<?php

namespace Igniter\Local\Tests\Http\Controllers;

use Igniter\Local\Models\Location;

it('loads locations page', function() {
    actingAsSuperUser()
        ->get(route('igniter.local.locations'))
        ->assertOk();
});

it('loads create location page', function() {
    actingAsSuperUser()
        ->get(route('igniter.local.locations', ['slug' => 'create']))
        ->assertOk();
});

it('loads edit location page', function() {
    $location = Location::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.local.locations', ['slug' => 'edit/'.$location->getKey()]))
        ->assertOk();
});

it('loads location settings page', function() {
    $location = Location::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.local.locations', ['slug' => 'settings/'.$location->getKey()]))
        ->assertOk();
});

it('loads location preview page', function() {
    $location = Location::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.local.locations', ['slug' => 'preview/'.$location->getKey()]))
        ->assertOk();
});

it('sets a default location', function() {
    $location = Location::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.local.locations'), [
            'default' => $location->getKey(),
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSetDefault',
        ]);

    Location::clearDefaultModel();
    expect(Location::getDefaultKey())->toBe($location->getKey());
});

it('creates location', function() {
    actingAsSuperUser()
        ->post(route('igniter.local.locations', ['slug' => 'create']), [
            'Location' => [
                'location_name' => 'Created Location',
                'location_email' => 'location@domain.tld',
                'location_telephone' => '1234567890',
                'location_address_1' => '123 Street',
                'location_city' => 'City',
                'location_state' => 'State',
                'location_postcode' => '12345',
                'location_country_id' => 1,
                'location_status' => 1,
                'is_auto_lat_lng' => 0,
                'location_lat' => '0.01',
                'location_lng' => '0.01',
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ]);

    expect(Location::where('location_name', 'Created Location')->exists())->toBeTrue();
});

it('updates location', function() {
    $location = Location::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.local.locations', ['slug' => 'edit/'.$location->getKey()]), [
            'Location' => [
                'location_name' => 'Updated Location',
                'location_email' => 'location@domain.tld',
                'location_telephone' => '1234567890',
                'location_address_1' => '123 Street',
                'location_city' => 'City',
                'location_state' => 'State',
                'location_postcode' => '12345',
                'location_country_id' => 1,
                'location_status' => 1,
                'is_auto_lat_lng' => 0,
                'location_lat' => '0.01',
                'location_lng' => '0.01',
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ]);

    expect(Location::where('location_name', 'Updated Location')->exists())->toBeTrue();
});

it('updates location settings', function() {
    $location = Location::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.local.locations', ['slug' => 'settings/'.$location->getKey()]), [
            'Location' => [
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ])
        ->assertOk();
});

it('deletes location', function() {
    $location = Location::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.local.locations', ['slug' => 'edit/'.$location->getKey()]), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onDelete',
        ]);

    expect(Location::where('location_id', $location->getKey())->exists())->toBeFalse();
});

