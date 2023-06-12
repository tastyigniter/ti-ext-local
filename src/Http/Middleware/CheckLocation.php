<?php

namespace Igniter\Local\Http\Middleware;

use Closure;
use Igniter\Flame\Igniter;
use Igniter\Local\Facades\AdminLocation;
use Igniter\Local\Facades\Location;
use Igniter\User\Facades\AdminAuth;
use Illuminate\Http\Request;

class CheckLocation
{
    public function handle(Request $request, Closure $next)
    {
        $location = null;

        if (Igniter::runningInAdmin() && AdminAuth::check()) {
            $location = AdminLocation::current();
        }

        if (!Igniter::runningInAdmin()) {
            $location = Location::current();
        }

        if (!is_null($location)) {
            $request->route()->setParameter('location', $location->permalink_slug);
        }

        return $next($request);
    }
}
