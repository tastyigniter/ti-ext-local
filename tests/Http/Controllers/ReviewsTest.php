<?php

namespace Igniter\Local\Tests\Http\Controllers;

use Igniter\Cart\Models\Order;
use Igniter\Local\Models\Review;

it('loads reviews page', function() {
    actingAsSuperUser()
        ->get(route('igniter.local.reviews'))
        ->assertOk();
});

it('loads create review page', function() {
    actingAsSuperUser()
        ->get(route('igniter.local.reviews', ['slug' => 'create']))
        ->assertOk();
});

it('loads edit review page', function() {
    $review = Review::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.local.reviews', ['slug' => 'edit/'.$review->getKey()]))
        ->assertOk();
});

it('creates review', function() {
    $order = Order::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.local.reviews', ['slug' => 'create']), [
            'Review' => [
                'customer_id' => 1,
                'location_id' => 1,
                'reviewable_type' => 'orders',
                'reviewable_id' => $order->getKey(),
                'quality' => 5,
                'delivery' => 5,
                'service' => 5,
                'review_text' => 'Great food!',
                'review_status' => 1,
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ]);

    expect(Review::where('review_text', 'Great food!')->exists())->toBeTrue();
});

it('updates review', function() {
    $review = Review::factory()->create();
    $order = Order::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.local.reviews', ['slug' => 'edit/'.$review->getKey()]), [
            'Review' => [
                'customer_id' => 1,
                'location_id' => 1,
                'reviewable_type' => 'orders',
                'reviewable_id' => $order->getKey(),
                'quality' => 5,
                'delivery' => 5,
                'service' => 5,
                'review_text' => 'Great food!',
                'review_status' => 1,
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onSave',
        ]);

    expect(Review::where('review_text', 'Great food!')->exists())->toBeTrue();
});

it('deletes review', function() {
    $review = Review::factory()->create();

    actingAsSuperUser()
        ->post(route('igniter.local.reviews', ['slug' => 'edit/'.$review->getKey()]), [], [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => 'onDelete',
        ]);

    expect(Review::where('review_id', $review->getKey())->exists())->toBeFalse();
});

