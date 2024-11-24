<?php

namespace Igniter\Local\Tests;

use Igniter\Flame\Geolite\Facades\Geocoder;
use Igniter\Flame\Geolite\Model\Location as UserPosition;
use Igniter\Local\Extension;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationArea;
use Igniter\Local\Models\Review;
use Igniter\Reservation\Models\Reservation;
use Igniter\User\Facades\Auth;
use Igniter\User\Models\Customer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Mockery;

beforeEach(function() {
    $this->extension = new Extension(app());
});

it('registers location middleware', function() {
    Route::shouldReceive('pushMiddlewareToGroup')
        ->with('igniter', \Igniter\Local\Http\Middleware\CheckLocation::class)
        ->once();

    $this->extension->register();
});

it('binds remember location area events', function() {
    Event::shouldReceive('listen')
        ->with('location.position.updated', Mockery::type('callable'))
        ->once();

    Event::shouldReceive('listen')
        ->with('location.area.updated', Mockery::type('callable'))
        ->once();

    Event::shouldReceive('listen')
        ->with(['igniter.user.login', 'igniter.socialite.login'], Mockery::type('callable'))
        ->once();

    Event::shouldReceive('listen')
        ->with('admin.form.extendFieldsBefore', Mockery::type('callable'))
        ->once();

    $this->extension->boot();
});

it('adds reviews relationship to reservation', function() {
    $this->extension->boot();

    $model = new Reservation();
    $review = new Review();

    expect($model->relation['morphMany']['review'])->not->toBeNull()
        ->toBe([Review::class, 'name' => 'reviewable'])
        ->and($review->getMorphClass())->toBe('reviews');
});

it('updates customer last area on location position updated', function() {
    $location = Mockery::mock(Location::class);
    $position = Mockery::mock(UserPosition::class);
    $oldPosition = Mockery::mock(UserPosition::class);

    $position->shouldReceive('format')->andReturn('new-position');
    $oldPosition->shouldReceive('format')->andReturn('old-position');

    Event::fake();

    $this->extension->boot();

    Event::dispatch('location.position.updated', [$location, $position, $oldPosition]);

    Event::assertDispatched('location.position.updated');
});

it('updates customer last area on location area updated', function() {
    $location = Mockery::mock(Location::class);
    $coveredArea = Mockery::mock(LocationArea::class);

    $coveredArea->shouldReceive('getKey')->andReturn(1);

    Event::fake();

    $this->extension->boot();

    Event::dispatch('location.area.updated', [$location, $coveredArea]);

    Event::assertDispatched('location.area.updated');
});

it('updates user position and nearby area on user login', function() {
    $locationArea = LocationArea::factory()->create([
        'location_id' => 1,
    ]);
    $customer = Customer::factory()->create([
        'customer_id' => 1,
        'last_location_area' => json_encode(['query' => 'test-query', 'areaId' => $locationArea->getKey()]),
    ]);
    Auth::shouldReceive('customer')->andReturn($customer);

    Geocoder::shouldReceive('geocode')->with('test-query')->andReturnSelf();
    Geocoder::shouldReceive('first')->andReturn(Mockery::mock(UserPosition::class));

    LocationFacade::shouldReceive('updateNearbyArea')->with($locationArea);

    Event::fake();

    $this->extension->boot();

    Event::dispatch('igniter.user.login');

    Event::assertDispatched('igniter.user.login');
});
