<?php

namespace Igniter\Local\Tests\Http\Requests;

use Igniter\Local\Http\Requests\LocationAreaRequest;

beforeEach(function() {
    $this->rules = (new LocationAreaRequest)->rules();
});

it('has required rule for name, priority, and status', function() {
    expect('required')->toBeIn(array_get($this->rules, 'type'))
        ->and('required')->toBeIn(array_get($this->rules, 'name'))
        ->and('required')->toBeIn(array_get($this->rules, 'boundaries.components.*.type'))
        ->and('required')->toBeIn(array_get($this->rules, 'boundaries.components.*.value'))
        ->and('required')->toBeIn(array_get($this->rules, 'boundaries.distance.*.type'))
        ->and('required')->toBeIn(array_get($this->rules, 'boundaries.distance.*.distance'))
        ->and('required')->toBeIn(array_get($this->rules, 'boundaries.distance.*.charge'))
        ->and('required')->toBeIn(array_get($this->rules, 'conditions.*.amount'))
        ->and('required')->toBeIn(array_get($this->rules, 'conditions.*.type'))
        ->and('required')->toBeIn(array_get($this->rules, 'conditions.*.total'));
});

it('has sometimes rule for inputs', function() {
    expect('sometimes')->toBeIn(array_get($this->rules, 'type'))
        ->and('sometimes')->toBeIn(array_get($this->rules, 'name'))
        ->and('sometimes')->toBeIn(array_get($this->rules, 'boundaries.components'))
        ->and('sometimes')->toBeIn(array_get($this->rules, 'boundaries.components.*.type'))
        ->and('sometimes')->toBeIn(array_get($this->rules, 'boundaries.components.*.value'))
        ->and('sometimes')->toBeIn(array_get($this->rules, 'boundaries.polygon'))
        ->and('sometimes')->toBeIn(array_get($this->rules, 'boundaries.distance.*.type'))
        ->and('sometimes')->toBeIn(array_get($this->rules, 'boundaries.distance.*.distance'))
        ->and('sometimes')->toBeIn(array_get($this->rules, 'boundaries.distance.*.charge'))
        ->and('sometimes')->toBeIn(array_get($this->rules, 'conditions'))
        ->and('sometimes')->toBeIn(array_get($this->rules, 'conditions.*.amount'))
        ->and('sometimes')->toBeIn(array_get($this->rules, 'conditions.*.type'))
        ->and('sometimes')->toBeIn(array_get($this->rules, 'conditions.*.total'));
});

it('has integer rule for area_id', function() {
    expect('integer')->toBeIn(array_get($this->rules, 'area_id'));
});

it('has string rule for type, components, and distance', function() {
    expect('string')->toBeIn(array_get($this->rules, 'type'))
        ->and('string')->toBeIn(array_get($this->rules, 'name'))
        ->and('string')->toBeIn(array_get($this->rules, 'boundaries.components.*.type'))
        ->and('string')->toBeIn(array_get($this->rules, 'boundaries.components.*.value'))
        ->and('string')->toBeIn(array_get($this->rules, 'boundaries.distance.*.type'));
});

it('has numeric rule for distance, charge, amount, and total', function() {
    expect('numeric')->toBeIn(array_get($this->rules, 'boundaries.distance.*.distance'))
        ->and('numeric')->toBeIn(array_get($this->rules, 'boundaries.distance.*.charge'))
        ->and('numeric')->toBeIn(array_get($this->rules, 'conditions.*.amount'))
        ->and('numeric')->toBeIn(array_get($this->rules, 'conditions.*.total'));
});

it('has alpha_dash rule for type', function() {
    expect('alpha_dash')->toBeIn(array_get($this->rules, 'conditions.*.type'));
});

it('has json rule for circle and vertices', function() {
    expect('json')->toBeIn(array_get($this->rules, 'boundaries.circle'))
        ->and('json')->toBeIn(array_get($this->rules, 'boundaries.vertices'));
});

it('has required_if rule for components, polygon, and circle', function() {
    expect('required_if:type,address')->toBeIn(array_get($this->rules, 'boundaries.components'))
        ->and('required_if:type,polygon')->toBeIn(array_get($this->rules, 'boundaries.polygon'))
        ->and('required_if:type,circle')->toBeIn(array_get($this->rules, 'boundaries.circle'));
});

it('has required_unless rule for vertices', function() {
    expect('required_unless:type,address')->toBeIn(array_get($this->rules, 'boundaries.vertices'));
});

it('has array rule for components, distance, and conditions', function() {
    expect('array')->toBeIn(array_get($this->rules, 'boundaries.components'))
        ->and('array')->toBeIn(array_get($this->rules, 'conditions'));
});
