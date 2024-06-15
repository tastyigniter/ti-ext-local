<?php

namespace Igniter\Local\Tests\Classes;

use DateInterval;
use DateTime;
use Igniter\Local\Classes\WorkingPeriod;
use Igniter\Local\Classes\WorkingSchedule;

it('creates correctly', function() {
    $workingSchedule = new WorkingSchedule('UTC', 5);

    expect($workingSchedule->minDays())->toBe(0)
        ->and($workingSchedule->days())->toBe(5);
});

it('creates with min and max future days correctly', function() {
    $workingSchedule = new WorkingSchedule('UTC', [5, 10]);

    expect($workingSchedule->minDays())->toBe(5)
        ->and($workingSchedule->days())->toBe(10);
});

it('fills correctly', function() {
    $workingSchedule = new WorkingSchedule('UTC', 5);

    $workingSchedule->fill([
        'periods' => [
            'monday' => [
                ['08:00', '12:00'],
                ['13:00', '17:00'],
            ],
        ],
        'exceptions' => [
            '2022-12-31' => [
                ['08:00', '12:00'],
            ],
        ],
    ]);

    expect($workingSchedule->getPeriods())->toHaveCount(7)
        ->and($workingSchedule->exceptions())->toHaveCount(1);
});

it('checks status correctly', function() {
    $workingSchedule = new WorkingSchedule('UTC', 0);

    $workingSchedule->fill([
        'periods' => [
            'monday' => [
                ['08:00', '12:00'],
                ['13:00', '17:00'],
            ],
        ],
        'exceptions' => [
            '2022-12-25' => [
                ['08:00', '12:00'],
            ],
            '2022-12-31' => [],
        ],
    ]);

    expect($workingSchedule->checkStatus(new DateTime('2022-12-25 10:00:00')))->toBe(WorkingPeriod::OPEN)
        ->and($workingSchedule->checkStatus(new DateTime('2022-12-31 20:00:00')))->toBe(WorkingPeriod::CLOSED)
        ->and($workingSchedule->checkStatus(new DateTime('2023-01-02 20:00:00')))->toBe(WorkingPeriod::CLOSED)
        ->and($workingSchedule->checkStatus(new DateTime('2023-01-02 10:00:00')))->toBe(WorkingPeriod::OPEN)
        ->and($workingSchedule->checkStatus(new DateTime('2023-01-02 07:00:00')))->toBe(WorkingPeriod::OPENING);
});

it('gets timeslot correctly', function() {
    $workingSchedule = new WorkingSchedule('UTC', 5);

    $workingSchedule->fill([
        'periods' => [
            'monday' => [
                ['08:00', '12:00'],
                ['13:00', '17:00'],
            ],
        ],
    ]);

    $timeslot = $this->travelTo(new DateTime('2023-01-02 10:00:00'), function() use ($workingSchedule) {
        return $workingSchedule->getTimeslot(15, new DateTime('2023-01-02 10:00:00'))->all();
    });

    expect($timeslot)->toBeArray()
        ->and($timeslot['2023-01-02'])->toHaveCount(22);
});

it('generates timeslot correctly', function() {
    $workingSchedule = new WorkingSchedule('UTC', 5);

    $workingSchedule->fill([
        'periods' => [
            'monday' => [
                ['08:00', '12:00'],
                ['13:00', '17:00'],
            ],
        ],
    ]);

    $timeslot = $this->travelTo(new DateTime('2023-01-02 10:00:00'), function() use ($workingSchedule) {
        return $workingSchedule->generateTimeslot(
            new DateTime('2023-01-02 10:00:00'),
            new DateInterval('PT15M')
        )->all();
    });

    expect($timeslot)->toBeArray()
        ->and($timeslot)->toHaveCount(22);
});
