<?php

namespace Igniter\Local\Tests\Http\Controllers;


use Igniter\Flame\Support\Facades\Igniter;

it('loads reviews settings page', function() {
    Igniter::partialMock()->shouldReceive('runningInAdmin')->andReturnTrue();
    actingAsSuperUser()
        ->get(route('igniter.system.extensions', ['slug' => 'edit/igniter/local/reviewsettings']))
        ->assertOk();
});

