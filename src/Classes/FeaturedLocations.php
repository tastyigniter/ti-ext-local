<?php

declare(strict_types=1);

namespace Igniter\Local\Classes;

use Carbon\CarbonInterface;
use Igniter\Flame\Geolite\Contracts\CoordinatesInterface;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\LocationArea;
use Illuminate\Support\Facades\Event;

class FeaturedLocations
{
    /** @var array<string, list<int>> Featured location IDs by date, for the request. */
    protected array $cache = [];

    /**
     * @return list<int>
     */
    public function ids(?CarbonInterface $date = null): array
    {
        $date ??= now();

        return $this->cache[$date->toDateString()] ??= $this->loadIds($date);
    }

    public function isFeatured(Location|int $location, ?CarbonInterface $date = null): bool
    {
        $locationId = $location instanceof Location ? (int)$location->getKey() : $location;

        return in_array($locationId, $this->ids($date), true);
    }

    /**
     * The featured locations to list first: those that deliver to the coordinates when
     * they're known, otherwise all of them.
     *
     * @return list<int>
     */
    public function boostedIds(?CoordinatesInterface $coordinates): array
    {
        $ids = $this->ids();
        if ($ids === [] || !$coordinates instanceof CoordinatesInterface) {
            return $ids;
        }

        return Location::query()
            ->whereIn('location_id', $ids)
            ->get()
            ->filter(fn(Location $location): bool => $location->searchDeliveryArea($coordinates) instanceof LocationArea)
            ->map(fn(Location $location): int => (int)$location->getKey())
            ->values()
            ->all();
    }

    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * @return list<int>
     */
    protected function loadIds(CarbonInterface $date): array
    {
        $ids = Location::query()->whereIsFeatured($date)->pluck('location_id')->all();

        foreach (Event::dispatch('igniter.local.featuredLocationIds', [$date]) as $extra) {
            $ids = array_merge($ids, (array)$extra);
        }

        $ids = array_values(array_unique(array_map(intval(...), $ids)));
        if ($ids === []) {
            return [];
        }

        return Location::query()
            ->whereIsEnabled()
            ->whereIn('location_id', $ids)
            ->orderBy('location_id')
            ->pluck('location_id')
            ->map(fn($id): int => (int)$id)
            ->all();
    }
}
