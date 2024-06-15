<?php

namespace Igniter\Local\Models\Concerns;

use Igniter\Flame\Geolite\Facades\Geocoder;
use Igniter\Local\Contracts\AreaInterface;
use Igniter\Local\Models\LocationArea;

trait HasDeliveryAreas
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $deliveryAreas;

    public static function bootHasDeliveryAreas()
    {
        static::extend(function(self $model) {
            $model->relation['hasMany']['delivery_areas'] = [LocationArea::class, 'delete' => true];

            $model->addPurgeable(['delivery_areas']);
        });

        self::saving(function(self $model) {
            $model->geocodeAddressOnSave();
        });

        self::saved(function(self $model) {
            $model->restorePurgedValues();

            if (array_key_exists('delivery_areas', $model->getAttributes())) {
                $model->addLocationAreas((array)array_get($model->getAttributes(), 'delivery_areas', []));
            }
        });
    }

    protected function geocodeAddressOnSave()
    {
        if (!$this->is_auto_lat_lng) {
            return;
        }

        if ($this->location_lat && $this->location_lng) {
            if (!$this->isDirty([
                'location_address_1',
                'location_address_2',
                'location_city',
                'location_state',
                'location_postcode',
                'location_country_id',
                'location_lat',
                'location_lng',
            ])) {
                return;
            }
        }

        $address = format_address($this->getAddress(), false);

        $geoLocation = Geocoder::geocode($address)->first();
        if ($geoLocation && $geoLocation->hasCoordinates()) {
            $this->location_lat = $geoLocation->getCoordinates()->getLatitude();
            $this->location_lng = $geoLocation->getCoordinates()->getLongitude();
        }
    }

    public function listDeliveryAreas()
    {
        return $this->delivery_areas->keyBy('area_id');
    }

    /**
     * @return \Igniter\Local\Contracts\AreaInterface|null
     */
    public function findDeliveryArea($areaId)
    {
        return $this->listDeliveryAreas()->get($areaId);
    }

    /**
     * @param \Igniter\Flame\Geolite\Contracts\CoordinatesInterface $coordinates
     * @return \Igniter\Local\Contracts\AreaInterface|null
     */
    public function searchOrDefaultDeliveryArea($coordinates)
    {
        if ($area = $this->searchDeliveryArea($coordinates)) {
            return $area;
        }

        return $this->delivery_areas->where('is_default', 1)->first();
    }

    /**
     * @param \Igniter\Flame\Geolite\Contracts\CoordinatesInterface $coordinates
     * @return \Igniter\Local\Contracts\AreaInterface|null
     */
    public function searchOrFirstDeliveryArea($coordinates)
    {
        if (!$area = $this->searchDeliveryArea($coordinates)) {
            $area = $this->delivery_areas->first();
        }

        return $area;
    }

    /**
     * @param \Igniter\Flame\Geolite\Contracts\CoordinatesInterface $coordinates
     * @return \Igniter\Local\Contracts\AreaInterface|null
     */
    public function searchDeliveryArea($coordinates)
    {
        if (!$coordinates) {
            return null;
        }

        return $this->delivery_areas
            ->sortBy('priority')
            ->first(function(AreaInterface $model) use ($coordinates) {
                return $model->checkBoundary($coordinates);
            });
    }

    public function getDistanceUnit()
    {
        return strtolower($this->distanceUnit ?? setting('distance_unit'));
    }

    //
    //
    //

    /**
     * Create a new or update existing location areas
     *
     * @param array $deliveryAreas
     *
     * @return bool
     */
    public function addLocationAreas($deliveryAreas)
    {
        $locationId = $this->getKey();
        if (!is_numeric($locationId)) {
            return false;
        }

        if (!is_array($deliveryAreas)) {
            return false;
        }

        $idsToKeep = [];
        foreach ($deliveryAreas as $area) {
            $locationArea = $this->delivery_areas()->firstOrNew([
                'area_id' => $area['area_id'] ?? null,
            ])->fill(array_except($area, ['area_id']));

            $locationArea->save();
            $idsToKeep[] = $locationArea->getKey();
        }

        $this->delivery_areas()->whereNotIn('area_id', $idsToKeep)->delete();

        return count($idsToKeep);
    }
}
