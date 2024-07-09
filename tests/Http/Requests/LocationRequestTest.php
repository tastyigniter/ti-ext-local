<?php

namespace Igniter\Local\Tests\Http\Requests;

use Igniter\Local\Http\Requests\LocationRequest;

beforeEach(function() {
    $this->rules = (new LocationRequest)->rules();
});

it('has required rule for location_name, location_email and ...', function() {
    expect('required')->toBeIn(array_get($this->rules, 'location_name'))
        ->and('required')->toBeIn(array_get($this->rules, 'location_email'))
        ->and('required')->toBeIn(array_get($this->rules, 'location_address_1'))
        ->and('required')->toBeIn(array_get($this->rules, 'is_auto_lat_lng'));
});

it('has required_if rule for location_lat and location_lng', function() {
    expect('required_if:is_auto_lat_lng,0')->toBeIn(array_get($this->rules, 'location_lat'))
        ->and('required_if:is_auto_lat_lng,0')->toBeIn(array_get($this->rules, 'location_lng'));
});

it('has string rule for location_name, location_telephone and ...', function() {
    expect('string')->toBeIn(array_get($this->rules, 'location_name'))
        ->and('string')->toBeIn(array_get($this->rules, 'location_telephone'))
        ->and('string')->toBeIn(array_get($this->rules, 'location_address_1'))
        ->and('string')->toBeIn(array_get($this->rules, 'location_address_2'))
        ->and('string')->toBeIn(array_get($this->rules, 'location_city'))
        ->and('string')->toBeIn(array_get($this->rules, 'location_state'))
        ->and('string')->toBeIn(array_get($this->rules, 'location_postcode'));
});

it('has sometimes rule for inputs', function() {
    expect('nullable')->toBeIn(array_get($this->rules, 'location_telephone'))
        ->and('required_if:is_auto_lat_lng,0')->toBeIn(array_get($this->rules, 'location_lat'))
        ->and('required_if:is_auto_lat_lng,0')->toBeIn(array_get($this->rules, 'location_lng'));
});

it('has max characters rule for inputs', function() {
    expect('max:96')->toBeIn(array_get($this->rules, 'location_email'))
        ->and('between:2,255')->toBeIn(array_get($this->rules, 'location_address_1'))
        ->and('max:255')->toBeIn(array_get($this->rules, 'location_address_2'))
        ->and('max:255')->toBeIn(array_get($this->rules, 'location_city'))
        ->and('max:255')->toBeIn(array_get($this->rules, 'location_state'))
        ->and('max:15')->toBeIn(array_get($this->rules, 'location_postcode'))
        ->and('max:3028')->toBeIn(array_get($this->rules, 'description'))
        ->and('max:255')->toBeIn(array_get($this->rules, 'permalink_slug'));
});

it('has boolean rule for inputs', function() {
    expect('boolean')->toBeIn(array_get($this->rules, 'is_auto_lat_lng'))
        ->and('boolean')->toBeIn(array_get($this->rules, 'location_status'))
        ->and('boolean')->toBeIn(array_get($this->rules, 'is_default'));
});

it('has alpha_dash rule for permalink_slug', function() {
    expect('alpha_dash')->toBeIn(array_get($this->rules, 'permalink_slug'));
});

it('has email rule for location_email', function() {
    expect('email:filter')->toBeIn(array_get($this->rules, 'location_email'));
});

it('has numeric rule for location_lat and location_lng', function() {
    expect('numeric')->toBeIn(array_get($this->rules, 'location_lat'))
        ->and('numeric')->toBeIn(array_get($this->rules, 'location_lng'));
});

it('has between rule for location_name', function() {
    expect('between:2,32')->toBeIn(array_get($this->rules, 'location_name'));
});
