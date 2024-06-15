<?php

namespace Igniter\Local\Tests\Classes;

use Igniter\Local\Classes\CoveredAreaCondition;

it('constructs correctly', function() {
    $condition = new CoveredAreaCondition([
        'type' => 'above',
        'amount' => 10,
        'total' => 100,
        'priority' => 1,
    ]);

    expect($condition->type)->toBe('above')
        ->and($condition->amount)->toBe(10.0)
        ->and($condition->total)->toBe(100.0)
        ->and($condition->priority)->toBe(1);
});

it('gets label correctly', function() {
    $condition = new CoveredAreaCondition([
        'type' => 'above',
        'amount' => 10,
        'total' => 100,
        'priority' => 1,
    ]);

    expect($condition->getLabel())->toBe('£10.00 above £100.00');
});

it('gets charge correctly', function() {
    $condition = new CoveredAreaCondition([
        'type' => 'above',
        'amount' => 10,
        'total' => 100,
        'priority' => 1,
    ]);

    expect($condition->getCharge())->toBe(10.0);
});

it('gets minimum total correctly', function() {
    $condition = new CoveredAreaCondition([
        'type' => 'above',
        'amount' => 10,
        'total' => 100,
        'priority' => 1,
    ]);

    expect($condition->getMinTotal())->toBe(100.0);
});

it('validates correctly', function() {
    $condition = new CoveredAreaCondition([
        'type' => 'above',
        'amount' => 10,
        'total' => 100,
        'priority' => 1,
    ]);

    expect($condition->isValid(150))->toBeTrue()
        ->and($condition->isValid(50))->toBeFalse();
});
