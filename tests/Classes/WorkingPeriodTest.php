<?php

namespace Igniter\Local\Tests\Classes;

use DateInterval;
use DateTime;
use Igniter\Local\Classes\WorkingPeriod;
use Igniter\Local\Classes\WorkingTime;

it('creates correctly', function() {
    $times = [
        ['08:00', '12:00'],
        ['13:00', '17:00'],
    ];

    $workingPeriod = WorkingPeriod::create($times);

    expect($workingPeriod->count())->toBe(2);
});

it('throws an exception when time overlaps', function() {
    $times = [
        ['08:00', '12:00'],
        ['11:00', '17:00'],
    ];

    WorkingPeriod::create($times);
})->throws('Igniter\Local\Exceptions\WorkingHourException');

it('checks if open at a specific time', function() {
    $times = [
        ['08:00', '12:00'],
        ['13:00', '17:00'],
    ];

    $workingPeriod = WorkingPeriod::create($times);

    expect($workingPeriod->isOpenAt(new WorkingTime(10, 00)))->toBeTrue()
        ->and($workingPeriod->isOpenAt(new WorkingTime(12, 30)))->toBeFalse();
});

it('gets open time at a specific time', function() {
    $times = [
        ['08:00', '12:00'],
        ['13:00', '17:00'],
    ];

    $workingPeriod = WorkingPeriod::create($times);

    expect($workingPeriod->openTimeAt(new WorkingTime(10, 00))->format())->toBe('08:00')
        ->and($workingPeriod->openTimeAt(new WorkingTime(14, 00))->format())->toBe('13:00');
});

it('gets close time at a specific time', function() {
    $times = [
        ['08:00', '12:00'],
        ['13:00', '17:00'],
    ];

    $workingPeriod = WorkingPeriod::create($times);

    expect($workingPeriod->closeTimeAt(new WorkingTime(10, 00))->format())->toBe('12:00')
        ->and($workingPeriod->closeTimeAt(new WorkingTime(14, 00))->format())->toBe('17:00');
});

it('gets next open time at a specific time', function() {
    $times = [
        ['08:00', '12:00'],
        ['13:00', '17:00'],
    ];

    $workingPeriod = WorkingPeriod::create($times);

    expect($workingPeriod->nextOpenAt(new WorkingTime(06, 00))->format())->toBe('08:00')
        ->and($workingPeriod->nextOpenAt(new WorkingTime(12, 30))->format())->toBe('13:00');
});

it('gets next close time at a specific time', function() {
    $times = [
        ['08:00', '12:00'],
        ['13:00', '17:00'],
    ];

    $workingPeriod = WorkingPeriod::create($times);

    expect($workingPeriod->nextCloseAt(new WorkingTime(10, 00))->format())->toBe('12:00')
        ->and($workingPeriod->nextCloseAt(new WorkingTime(14, 00))->format())->toBe('17:00');
});

it('checks if opens all day', function() {
    $times = [
        ['00:00', '23:59'],
    ];

    $workingPeriod = WorkingPeriod::create($times);

    expect($workingPeriod->opensAllDay())->toBeTrue();
});

it('checks if closes late', function() {
    $times = [
        ['08:00', '12:00'],
        ['13:00', '01:00'],
    ];

    $workingPeriod = WorkingPeriod::create($times);

    expect($workingPeriod->closesLate())->toBeTrue();
});

it('checks if opens late at a specific time', function() {
    $times = [
        ['08:00', '12:00'],
        ['13:00', '01:00'],
    ];

    $workingPeriod = WorkingPeriod::create($times);

    expect($workingPeriod->opensLateAt(new WorkingTime(00, 30)))->toBeTrue()
        ->and($workingPeriod->opensLateAt(new WorkingTime(10, 00)))->toBeFalse();
});

it('generates timeslot', function() {
    $times = [
        ['08:00', '12:00'],
        ['13:00', '17:00'],
    ];

    $workingPeriod = WorkingPeriod::create($times);

    $timeslot = $workingPeriod->timeslot(
        new DateTime('2022-12-31 10:00:00'),
        new DateInterval('PT15M'),
    )->all();

    expect($timeslot)->toBeArray()
        ->and($timeslot[0]->format('H:i'))->toBe('08:00')
        ->and($timeslot[1]->format('H:i'))->toBe('08:15');
});
