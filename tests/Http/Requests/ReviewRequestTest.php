<?php

namespace Igniter\Local\Tests\Http\Requests;

use Igniter\Local\Http\Requests\ReviewRequest;

beforeEach(function() {
    $request = (new ReviewRequest)->merge([
        'reviewable_type' => 'type',
        'reviewable_id' => 1,
    ]);
    $this->rules = ($request)->rules();
});

it('has required rule for inputs', function() {
    expect('required')->toBeIn(array_get($this->rules, 'location_id'))
        ->and('required')->toBeIn(array_get($this->rules, 'customer_id'))
        ->and('required')->toBeIn(array_get($this->rules, 'reviewable_type'))
        ->and('required')->toBeIn(array_get($this->rules, 'reviewable_id'))
        ->and('required')->toBeIn(array_get($this->rules, 'quality'))
        ->and('required')->toBeIn(array_get($this->rules, 'delivery'))
        ->and('required')->toBeIn(array_get($this->rules, 'service'))
        ->and('required')->toBeIn(array_get($this->rules, 'review_text'))
        ->and('required')->toBeIn(array_get($this->rules, 'review_status'));
});

it('has integer rule for quality, delivery, and service, location_id and customer_id', function() {
    expect('integer')->toBeIn(array_get($this->rules, 'location_id'))
        ->and('integer')->toBeIn(array_get($this->rules, 'customer_id'))
        ->and('integer')->toBeIn(array_get($this->rules, 'quality'))
        ->and('integer')->toBeIn(array_get($this->rules, 'delivery'))
        ->and('integer')->toBeIn(array_get($this->rules, 'service'));
});

it('has between rule for review_text', function() {
    expect('between:2,1028')->toBeIn(array_get($this->rules, 'review_text'));
});

it('has boolean rule for review_status', function() {
    expect('boolean')->toBeIn(array_get($this->rules, 'review_status'));
});

it('has exists rule for reviewable_id', function() {
    expect('exists:type,type_id')->toBeIn(array_get($this->rules, 'reviewable_id'));
});

