<?php

namespace Igniter\Local\Tests\Http\Middleware;

use Igniter\Local\Facades\Location;
use Igniter\Local\Http\Middleware\CheckLocation;
use Igniter\Local\Models\Location as LocationModel;
use Igniter\User\Facades\AdminAuth;
use Igniter\User\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\HeaderBag;

it('handles request correctly', function() {
    $location = LocationModel::factory()->create([
        'permalink_slug' => 'test-location'
    ]);

    $request = Request::create('/');
    $route = (new Route('GET', '/{location}', []))->bind($request);
    $request->setRouteResolver(function() use ($route) {
        return $route;
    });

    Location::shouldReceive('currentOrDefault')->andReturn($location);

    $response = (new CheckLocation())->handle($request, function($req) {
        return true;
    });

    expect($response)->toBeTrue()
        ->and($request->route()->parameter('location'))->toBe($location->permalink_slug);
});

it('redirects when location route parameter does not match current location slug', function() {
    $location = LocationModel::factory()->create([
        'permalink_slug' => 'test-location'
    ]);

    $request = Request::create('/wrong-location');
    $route = (new Route('GET', '/{location}', []))->bind($request);
    $request->setRouteResolver(function() use ($route) {
        return $route;
    });

    Location::shouldReceive('currentOrDefault')->andReturn($location);

    $response = (new CheckLocation())->handle($request, function($req) {
        return $req;
    });

    expect($response)->toBeInstanceOf(RedirectResponse::class);
});

it('checks admin location correctly', function() {
    $user = User::factory()->create();
    $location = LocationModel::factory()->create([
        'permalink_slug' => 'test-location'
    ]);

    $mockRequest = $this->mock(\Illuminate\Http\Request::class);
    $mockRequest->shouldReceive('path')->andReturn('admin/dashboard');
    $mockRequest->shouldReceive('decodedPath')->andReturn('admin/dashboard');
    $mockRequest->shouldReceive('setUserResolver')->andReturnNull();
    $mockRequest->shouldReceive('headers')->andReturn(new HeaderBag());
    $route = (new Route('GET', '/', []))->bind($mockRequest);
    $mockRequest->shouldReceive('route')->andReturn($route);

    app()->instance('request', $mockRequest);

    AdminAuth::shouldReceive('check')->andReturn(true);
    Location::shouldReceive('resetSession')->andReturnNull();
    Location::shouldReceive('current')->andReturn($location);
    AdminAuth::shouldReceive('user')->andReturn($user);

    (new CheckLocation())->handle(request(), function($req) {
        return $req;
    });

    expect($route->parameter('location'))->toBe($location->permalink_slug);
});
