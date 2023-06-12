<?php

namespace Igniter\Local\Traits;

use Igniter\Local\Facades\AdminLocation;
use Igniter\Local\Models\Location;

trait LocationAwareWidget
{
    protected function isLocationAware($config)
    {
        $locationAware = $config['locationAware'] ?? false;

        return $locationAware && $this->controller->getUserLocation();
    }

    /**
     * Apply location scope where required
     */
    protected function locationApplyScope($query)
    {
        if (is_null($ids = AdminLocation::getAll())) {
            return;
        }

        $model = $query->getModel();
        if ($model instanceof Location) {
            $query->whereIn('location_id', $ids);

            return;
        }

        if (!in_array(\Igniter\Local\Models\Concerns\Locationable::class, class_uses($model))) {
            return;
        }

        $query->whereHasLocation($ids);
    }
}
