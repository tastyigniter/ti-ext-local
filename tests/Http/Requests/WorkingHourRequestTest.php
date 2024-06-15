<?php

namespace Igniter\Local\Tests\Http\Requests;

use Igniter\Local\Http\Requests\WorkingHourRequest;

beforeEach(function() {
    $this->rules = (new WorkingHourRequest)->rules();
});

it('has required_if rule for flexible.*.status', function() {
    expect('required_if:type,daily')->toBeIn(array_get($this->rules, 'days.*'))
        ->and('required_if:type,daily')->toBeIn(array_get($this->rules, 'open'))
        ->and('required_if:type,daily')->toBeIn(array_get($this->rules, 'close'))
        ->and('required_if:type,timesheet')->toBeIn(array_get($this->rules, 'timesheet'))
        ->and('required_if:type,flexible')->toBeIn(array_get($this->rules, 'flexible'))
        ->and('required_if:type,flexible')->toBeIn(array_get($this->rules, 'flexible.*.day'))
        ->and('required_if:type,flexible')->toBeIn(array_get($this->rules, 'flexible.*.hours'))
        ->and('required_if:type,flexible')->toBeIn(array_get($this->rules, 'flexible.*.status'));
});

it('has sometimes rule for inputs', function() {
    expect('sometimes')->toBeIn(array_get($this->rules, 'flexible.*.status'));
});

it('has alpha_dash rule for type', function() {
    expect('alpha_dash')->toBeIn(array_get($this->rules, 'type'));
});

it('has in rule for type', function() {
    expect('in:24_7,daily,timesheet,flexible')->toBeIn(array_get($this->rules, 'type'));
});

it('has integer rule for days', function() {
    expect('integer')->toBeIn(array_get($this->rules, 'days.*'));
});

it('has between rule for days', function() {
    expect('between:0,7')->toBeIn(array_get($this->rules, 'days.*'));
});

it('has date_format rule for open and close', function() {
    expect('date_format:H:i')->toBeIn(array_get($this->rules, 'open'))
        ->and('date_format:H:i')->toBeIn(array_get($this->rules, 'close'));
});

it('has string rule for timesheet', function() {
    expect('string')->toBeIn(array_get($this->rules, 'timesheet'));
});

it('has array rule for flexible', function() {
    expect('array')->toBeIn(array_get($this->rules, 'flexible'));
});

it('has numeric rule for flexible.*.day', function() {
    expect('numeric')->toBeIn(array_get($this->rules, 'flexible.*.day'));
});
