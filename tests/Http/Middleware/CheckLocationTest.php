<?php

namespace Igniter\Local\Tests\Http\Middleware;

use Igniter\Local\Facades\Location;
use Igniter\Local\Http\Middleware\CheckLocation;
use Igniter\Local\Models\Location as LocationModel;
use Igniter\User\Facades\AdminAuth;
use Igniter\User\Models\User;
use Illuminate\Support\Facades\Route;

it('handles request correctly', function() {
    Route::get('test-route/{location}', fn() => 'ok')->middleware(CheckLocation::class);

    $location = LocationModel::factory()->create([
        'permalink_slug' => 'test-location',
    ]);

    Location::shouldReceive('currentOrDefault')->andReturn($location);

    $this->get('test-route/test-location')->assertStatus(200);

    expect(request()->route('location'))->toBe($location->permalink_slug);
});

it('redirects when location route parameter does not match current location slug', function() {
    Route::get('test-route/{location}', fn() => 'ok')->middleware(CheckLocation::class);

    $location = LocationModel::factory()->create([
        'permalink_slug' => 'test-location',
    ]);

    Location::shouldReceive('currentOrDefault')->andReturn($location);

    $this->get('test-route/wrong-location')
        ->assertStatus(302);
});

it('checks admin location correctly', function() {
    Route::get('admin/test-route', fn() => 'ok')->middleware(CheckLocation::class);

    $user = User::factory()->superUser()->create();
    $location = LocationModel::factory()->create([
        'permalink_slug' => 'test-location',
    ]);

    AdminAuth::shouldReceive('check')->andReturn(true);
    Location::shouldReceive('resetSession')->andReturnNull();
    Location::shouldReceive('current')->andReturn($location);
    AdminAuth::shouldReceive('user')->andReturn($user);

    $this->get('admin/test-route');

    expect(request()->route('location'))->toBe($location->permalink_slug);
});
