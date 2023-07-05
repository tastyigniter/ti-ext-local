<?php

namespace Igniter\Local\Traits;

use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\Local\Models\Location;

trait LocationAwareWidget
{
    protected function isLocationAware($config)
    {
        $locationAware = $config['locationAware'] ?? false;

        return $locationAware && LocationFacade::check();
    }

    /**
     * Apply location scope where required
     */
    protected function locationApplyScope($query)
    {
        $model = $query->getModel();
        if (!$model instanceof Location && !in_array(Locationable::class, class_uses($model))) {
            return;
        }

        if (empty($ids = LocationFacade::currentOrAssigned())) {
            return;
        }

        $model instanceof Location
            ? $query->whereIn('location_id', $ids)
            : $query->whereHasLocation($ids);
    }
}
