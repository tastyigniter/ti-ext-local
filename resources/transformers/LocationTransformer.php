<?php namespace Igniter\Local\Resources\Transformers;

class LocationTransformer extends \Illuminate\Http\Resources\Json\Resource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}