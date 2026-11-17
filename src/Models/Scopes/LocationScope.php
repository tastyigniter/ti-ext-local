<?php

declare(strict_types=1);

namespace Igniter\Local\Models\Scopes;

use Carbon\CarbonInterface;
use Igniter\Flame\Database\Scope;
use Illuminate\Database\Eloquent\Builder;

class LocationScope extends Scope
{
    public function addApplyPosition()
    {
        return fn(Builder $builder, array $position) => $builder->selectDistance($position['latitude'], $position['longitude']);
    }

    public function addSelectDistance()
    {
        return function(Builder $builder, $latitude = null, $longitude = null) {
            if (setting('distance_unit') === 'km') {
                $sql = '( 6371 * acos( cos( radians(?) ) * cos( radians( location_lat ) ) *';
            } else {
                $sql = '( 3959 * acos( cos( radians(?) ) * cos( radians( location_lat ) ) *';
            }

            $sql .= ' cos( radians( location_lng ) - radians(?) ) + sin( radians(?) ) *';
            $sql .= ' sin( radians( location_lat ) ) ) ) AS distance';

            return $builder->selectRaw($sql, [$latitude, $longitude, $latitude]);
        };
    }

    public function addWhereIsFeatured()
    {
        return function(Builder $builder, ?CarbonInterface $date = null) {
            $day = ($date ?? now())->toDateString();

            return $builder->where('is_featured', true)
                ->where(fn($query) => $query->whereNull('featured_from')->orWhere('featured_from', '<=', $day))
                ->where(fn($query) => $query->whereNull('featured_until')->orWhere('featured_until', '>=', $day));
        };
    }
}
