<?php

namespace Tests\Admin\Requests;

use Igniter\Local\Http\Requests\LocationRequest;

it('has required rule for location_name, location_email and ...', function() {
    expect('required')->toBeIn(array_get((new LocationRequest)->rules(), 'location_name'))
        ->and('required')->toBeIn(array_get((new LocationRequest)->rules(), 'location_email'))
        ->and('required')->toBeIn(array_get((new LocationRequest)->rules(), 'location_address_1'))
        ->and('required')->toBeIn(array_get((new LocationRequest)->rules(), 'is_auto_lat_lng'));
});

it('has sometimes rule for inputs', function() {
    expect('sometimes')->toBeIn(array_get((new LocationRequest)->rules(), 'location_telephone'))
        ->and('required_if:is_auto_lat_lng,0')->toBeIn(array_get((new LocationRequest)->rules(), 'location_lat'))
        ->and('required_if:is_auto_lat_lng,0')->toBeIn(array_get((new LocationRequest)->rules(), 'location_lng'));
});

it('has max characters rule for inputs', function() {
    expect('max:96')->toBeIn(array_get((new LocationRequest)->rules(), 'location_email'))
        ->and('between:2,255')->toBeIn(array_get((new LocationRequest)->rules(), 'location_address_1'))
        ->and('max:255')->toBeIn(array_get((new LocationRequest)->rules(), 'location_address_2'))
        ->and('max:255')->toBeIn(array_get((new LocationRequest)->rules(), 'location_city'))
        ->and('max:255')->toBeIn(array_get((new LocationRequest)->rules(), 'location_state'))
        ->and('max:15')->toBeIn(array_get((new LocationRequest)->rules(), 'location_postcode'))
        ->and('max:3028')->toBeIn(array_get((new LocationRequest)->rules(), 'description'))
        ->and('max:255')->toBeIn(array_get((new LocationRequest)->rules(), 'permalink_slug'));
});
