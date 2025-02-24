<?php

declare(strict_types=1);

namespace Igniter\Local\Tests\Listeners;

use DateTime;
use Igniter\Cart\Models\Order;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Classes\WorkingSchedule;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Local\Listeners\MaxOrderPerTimeslotReached;
use Igniter\Local\Models\Location;

beforeEach(function(): void {
    MaxOrderPerTimeslotReached::$ordersCache = [];
});

it('bails when working schedule type is opening', function(): void {
    $workingSchedule = new WorkingSchedule('UTC', [1, 1]);
    $workingSchedule->setType(Location::OPENING);

    $listener = new MaxOrderPerTimeslotReached();
    $timeslot = new DateTime('2023-01-01 12:00:00');

    $result = $listener->timeslotValid($workingSchedule, $timeslot);

    expect($result)->toBeNull();
});

it('returns true when limit orders is disabled', function(): void {
    $location = mock(Location::class)->makePartial();
    $location->shouldReceive('getSettings')->with('checkout.limit_orders')->andReturnFalse();
    LocationFacade::shouldReceive('current')->andReturn($location);

    $workingSchedule = new WorkingSchedule('UTC', [1, 1]);
    $workingSchedule->setType(Location::DELIVERY);

    $listener = new MaxOrderPerTimeslotReached();
    $timeslot = new DateTime('2023-01-01 12:00:00');

    $result = $listener->timeslotValid($workingSchedule, $timeslot);

    expect($result)->toBeNull();
});

it('returns false when timeslot exceeds max orders for delivery', function(): void {
    $location = mock(Location::class)->makePartial();
    $location->shouldReceive('getSettings')->with('checkout.limit_orders')->andReturnTrue();
    $location->shouldReceive('getSettings')->with('checkout.limit_orders_count', 50)->andReturn(1);
    $location->shouldReceive('getOrderTimeInterval')->with(Location::DELIVERY)->andReturn(15);
    LocationFacade::shouldReceive('getId')->andReturn(1);
    LocationFacade::shouldReceive('current')->andReturn($location);
    Order::factory()->count(3)->create([
        'location_id' => 1,
        'status_id' => setting('default_order_status'),
        'order_date' => '2023-01-01',
        'order_time' => '12:00:00',
        'order_type' => Location::DELIVERY,
    ]);
    $timeslot = new DateTime('2023-01-01 12:00:00');

    $workingSchedule = new WorkingSchedule('UTC', [1, 1]);
    $workingSchedule->setType(Location::DELIVERY);

    $listener = new MaxOrderPerTimeslotReached();

    $result = $listener->timeslotValid($workingSchedule, $timeslot);

    expect($result)->toBeFalse();
});

it('throws exception when order exceeds max orders for pickup', function(): void {
    $location = mock(Location::class)->makePartial();
    $location->shouldReceive('getSettings')->with('checkout.limit_orders')->andReturnTrue();
    $location->shouldReceive('getSettings')->with('checkout.limit_orders_count', 50)->andReturn(1);
    $location->shouldReceive('getOrderTimeInterval')->with(Location::DELIVERY)->andReturn(15);
    LocationFacade::shouldReceive('getId')->andReturn(1);
    LocationFacade::shouldReceive('current')->andReturn($location);
    $orders = Order::factory()->count(3)->create([
        'location_id' => 1,
        'status_id' => setting('default_order_status'),
        'order_date' => '2023-01-01',
        'order_time' => '12:00:00',
        'order_type' => Location::DELIVERY,
    ]);

    $listener = new MaxOrderPerTimeslotReached();

    expect(fn() => $listener->beforeSaveOrder($orders->last(), []))->toThrow(ApplicationException::class);
});

it('returns true when timeslot does not exceed max orders', function(): void {
    $location = mock(Location::class)->makePartial();
    $location->shouldReceive('getSettings')->with('checkout.limit_orders')->andReturnTrue();
    $location->shouldReceive('getOrderTimeInterval')->with(Location::DELIVERY)->andReturn(15);
    LocationFacade::shouldReceive('getId')->andReturn(1);
    LocationFacade::shouldReceive('current')->andReturn($location);

    $workingSchedule = new WorkingSchedule('UTC', [1, 1]);
    $workingSchedule->setType(Location::DELIVERY);

    $listener = new MaxOrderPerTimeslotReached();
    $timeslot = new DateTime('2023-01-01 12:00:00');

    $result = $listener->timeslotValid($workingSchedule, $timeslot);

    expect($result)->toBeNull();

    // test coverage for cached timeslots
    $listener->timeslotValid($workingSchedule, $timeslot);
});
