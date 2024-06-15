<?php

namespace Igniter\Local\Tests\AutomationRules\Conditions;

use Igniter\Automation\Models\RuleCondition;
use Igniter\Cart\Models\Order;
use Igniter\Local\AutomationRules\Conditions\ReviewCount;

it('defines model attributes correctly', function() {
    $reviewCount = new ReviewCount();

    $attributes = $reviewCount->defineModelAttributes();

    expect($attributes)->toHaveKeys([
        'review_count',
    ]);
});

it('counts reviews correctly', function() {
    $order = Order::factory()->hasReview(1)->create();

    $reviewCount = new ReviewCount();

    expect($reviewCount->getReviewCountAttribute(null, $order))->toBe(1);
});

it('evaluates isTrue when no reviews correctly', function() {
    $order = Order::factory()->create();

    $orderAttribute = new ReviewCount(new RuleCondition([
        'options' => [
            ['attribute' => 'review_count', 'operator' => 'is', 'value' => 1],
        ],
    ]));

    $params = ['order' => $order];
    expect($orderAttribute->isTrue($params))->toBeFalse();
});

it('evaluates isTrue when review count is 1 correctly', function() {
    $order = Order::factory()->hasReview(1)->create();

    $orderAttribute = new ReviewCount(new RuleCondition([
        'options' => [
            ['attribute' => 'review_count', 'operator' => 'is', 'value' => 1],
        ],
    ]));

    $params = ['order' => $order];
    expect($orderAttribute->isTrue($params))->toBeTrue();
});

it('evaluates isTrue when review count is more than 1 correctly', function() {
    $order = Order::factory()->hasReview(3)->create();

    $orderAttribute = new ReviewCount(new RuleCondition([
        'options' => [
            ['attribute' => 'review_count', 'operator' => 'greater', 'value' => 1],
        ],
    ]));

    $params = ['order' => $order];
    expect($orderAttribute->isTrue($params))->toBeTrue();
});
