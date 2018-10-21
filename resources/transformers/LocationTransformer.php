<?php namespace Igniter\Local\Resources\Transformers;

use Admin\Models\Locations_model;

class LocationTransformer extends \League\Fractal\TransformerAbstract
{
    public function transform(Locations_model $model)
    {
        return $model->toArray();
    }
}