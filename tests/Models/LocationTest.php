<?php

namespace Igniter\Local\Tests\Models;

use Igniter\Flame\Database\Attach\HasMedia;
use Igniter\Flame\Database\Traits\HasPermalink;
use Igniter\Flame\Geolite\Facades\Geocoder;
use Igniter\Local\Models\Concerns\HasDeliveryAreas;
use Igniter\Local\Models\Concerns\HasWorkingHours;
use Igniter\Local\Models\Concerns\LocationHelpers;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\Scopes\LocationScope;
use Igniter\System\Models\Concerns\Defaultable;
use Igniter\System\Models\Concerns\HasCountry;
use Igniter\System\Models\Concerns\Switchable;

it('geocodes address on save', function() {
    $location = Location::factory()->create();

    $lat = 37.7749295;
    $lng = -122.4194155;
    Geocoder::shouldReceive('geocode')->andReturn(collect([
        \Igniter\Flame\Geolite\Model\Location::createFromArray([
            'latitude' => $lat,
            'longitude' => $lng,
        ]),
    ]));

    $location->is_auto_lat_lng = true;
    $location->location_lat = null;
    $location->location_lng = null;
    $location->save();

    expect($location->location_lat)->toBe($lat)
        ->and($location->location_lng)->toBe($lng);
});

it('adds delivery areas on save', function() {
    $location = Location::factory()->create();

    $location->delivery_areas = [
        ['area_id' => 1, 'conditions' => ['min_total' => 10]],
        ['area_id' => 2, 'conditions' => ['min_total' => 20]],
    ];

    $location->save();

    expect($location->delivery_areas()->count())->toBe(2);
});

it('applies filters to query builder', function() {
    $query = Location::query()->applyFilters([
        'enabled' => 1,
        'position' => [['latitude' => 1, 'longitude' => 2]],
        'sort' => 'location_name desc',
        'search' => 'Location category',
    ]);

    expect($query->toSql())
        ->toContain('`locations`.`location_status` = ?')
        ->toContain('radians( location_lat )', 'radians( location_lng )', ' AS distance')
        ->toContain('lower(location_name) like ?', 'lower(location_address_1) like ?', 'lower(description) like ?')
        ->toContain('order by `location_name` desc');
});

it('configures location model correctly', function() {
    $location = new Location();

    expect(class_uses_recursive($location))
        ->toContain(Defaultable::class)
        ->toContain(HasCountry::class)
        ->toContain(HasDeliveryAreas::class)
        ->toContain(HasMedia::class)
        ->toContain(HasPermalink::class)
        ->toContain(HasWorkingHours::class)
        ->toContain(LocationHelpers::class)
        ->toContain(Switchable::class)
        ->and($location->getTable())->toBe('locations')
        ->and($location->getKeyName())->toBe('location_id')
        ->and($location->timestamps)->toBeTrue()
        ->and($location->getAppends())->toContain('location_thumb')
        ->and($location->getCasts())->toHaveKeys(['location_lat', 'location_lng'])
        ->and($location->getMorphClass())->toBe('locations')
        ->and($location->getGlobalScopes())->toHaveKey(LocationScope::class)
        ->and($location->relation)->toBeArray()
        ->and($location->getPurgeableAttributes())->toContain('options', 'delivery_areas')
        ->and($location->permalinkable)->toBe([
            'permalink_slug' => [
                'source' => 'location_name',
                'controller' => 'local',
            ],
        ])
        ->and($location->mediable)->toBe([
            'thumb',
            'gallery' => ['multiple' => true],
        ]);
});
