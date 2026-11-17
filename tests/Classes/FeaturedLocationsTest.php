<?php

declare(strict_types=1);

namespace Igniter\Local\Tests\Classes;

use Carbon\Carbon;
use Igniter\Flame\Geolite\Model\Coordinates;
use Igniter\Local\Classes\FeaturedLocations;
use Igniter\Local\Models\Location;
use Illuminate\Support\Facades\Event;

beforeEach(function(): void {
    Carbon::setTestNow('2026-12-10 12:00:00');
    Location::query()->update(['is_featured' => false]);
    $this->featured = new FeaturedLocations;
});

afterEach(fn() => Carbon::setTestNow());

it('features enabled locations admins feature for the day', function(): void {
    $always = Location::factory()->create(['location_status' => true, 'is_featured' => true]);
    $december = Location::factory()->create(['location_status' => true, 'is_featured' => true, 'featured_from' => '2026-12-01', 'featured_until' => '2026-12-31']);
    $later = Location::factory()->create(['location_status' => true, 'is_featured' => true, 'featured_from' => '2026-12-20']);
    Location::factory()->create(['location_status' => false, 'is_featured' => true]);
    Location::factory()->create(['location_status' => true, 'is_featured' => false]);

    expect($this->featured->ids())->toBe([$always->getKey(), $december->getKey()])
        ->and($this->featured->ids(Carbon::parse('2027-01-02')))->toBe([$always->getKey(), $later->getKey()])
        ->and(Location::query()->whereIsFeatured()->count())->toBe(3)
        ->and($december->isFeatured())->toBeTrue()
        ->and($later->isFeatured())->toBeFalse();
});

it('adds locations extensions feature', function(): void {
    $extra = Location::factory()->create(['location_status' => true]);
    $disabled = Location::factory()->create(['location_status' => false]);
    Event::listen('igniter.local.featuredLocationIds', fn(): array => [$extra->getKey(), (string)$disabled->getKey()]);

    expect($this->featured->ids())->toBe([$extra->getKey()])
        ->and($this->featured->isFeatured($extra->getKey()))->toBeTrue();
});

it('boosts featured locations that deliver to the coordinates', function(): void {
    $delivers = Location::factory()->create(['location_status' => true, 'is_featured' => true]);
    $delivers->delivery_areas()->create([
        'name' => 'Everywhere', 'type' => 'circle', 'is_default' => 1,
        'boundaries' => ['circle' => json_encode(['lat' => 51.5, 'lng' => -0.12, 'radius' => 50000])],
    ]);
    $farAway = Location::factory()->create(['location_status' => true, 'is_featured' => true]);

    expect($this->featured->boostedIds(new Coordinates(51.5, -0.12)))->toBe([$delivers->getKey()])
        ->and($this->featured->boostedIds(null))->toBe([$delivers->getKey(), $farAway->getKey()]);
});

it('shows the featured fields on the location form', function(): void {
    $location = Location::factory()->create();

    actingAsSuperUser()
        ->get(route('igniter.local.locations', ['slug' => 'edit/'.$location->getKey()]))
        ->assertOk()
        ->assertSee('Location[is_featured]', false)
        ->assertSee('Location[featured_until]', false);
});
