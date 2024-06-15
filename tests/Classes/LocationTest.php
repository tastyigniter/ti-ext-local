<?php

namespace Igniter\Local\Tests\Classes;

use Igniter\Cart\Classes\AbstractOrderType;
use Igniter\Flame\Geolite\Model\Location as UserLocation;
use Igniter\Local\Classes\CoveredArea;
use Igniter\Local\Classes\Location;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Local\Models\Location as LocationModel;
use Igniter\Local\Models\LocationArea;
use Illuminate\Support\Facades\Event;
use Mockery;

it('updates nearby area correctly', function() {
    $location = new Location;
    $area = Mockery::mock(LocationArea::class);
    $area->shouldReceive('extendableGet')->with('location')->andReturn(new LocationModel);
    $area->shouldReceive('getKey')->andReturn(1);

    $location->updateNearbyArea($area);

    expect($location->coveredArea())->toBeInstanceOf(CoveredArea::class);
});

it('updates order type correctly', function() {
    $location = new Location;

    $location->updateOrderType(LocationModel::COLLECTION);

    expect($location->orderType())->toBe(LocationModel::COLLECTION);
});

it('updates user position correctly', function() {
    $location = new Location;
    $userPosition = new UserLocation('google', []);

    $location->updateUserPosition($userPosition);

    expect($location->userPosition())->toBe($userPosition);
});

it('updates schedule time slot correctly', function() {
    Event::fake();

    $location = new Location;

    $location->updateScheduleTimeSlot('2022-12-31 12:00:00', false);

    Event::assertDispatched('location.timeslot.updated');
});

it('checks order type correctly', function() {
    $location = new Location;
    $location->setModel(new LocationModel);

    expect($location->checkOrderType(LocationModel::DELIVERY))->toBeTrue();
});

it('gets order type correctly', function() {
    $location = new Location;
    $location->setModel(new LocationModel);

    expect($location->getOrderType(LocationModel::DELIVERY))->toBeInstanceOf(AbstractOrderType::class);
});

it('gets minimum order total correctly', function() {
    $location = new Location;
    $location->setModel(new LocationModel);

    LocationFacade::shouldReceive('coveredArea->minimumOrderTotal')->andReturn(10.0);

    expect($location->minimumOrderTotal(LocationModel::DELIVERY))->toBeNumeric();
});

it('checks minimum order total correctly', function() {
    $location = new Location;
    $location->setModel(new LocationModel);

    LocationFacade::shouldReceive('coveredArea->minimumOrderTotal')->andReturn(10.0);

    expect($location->checkMinimumOrderTotal(100, LocationModel::DELIVERY))->toBeBool();
});

it('checks order time correctly', function() {
    $location = new Location;
    $location->setModel(new LocationModel(['location_id' => 1]));

    expect($location->checkOrderTime(now()->setHour(12), LocationModel::DELIVERY))->toBeBool();
});

it('checks delivery coverage correctly', function() {
    $location = new Location;
    $location->setModel(new LocationModel);
    $userPosition = new UserLocation('google', [
        'latitude' => 0.01,
        'longitude' => 0.01,
    ]);

    expect($location->checkDeliveryCoverage($userPosition))->toBeBool();
});
