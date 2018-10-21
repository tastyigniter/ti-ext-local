<?php namespace Igniter\Local\Resources\Transformers;

use Admin\Models\Menus_model;

class MenuTransformer extends \League\Fractal\TransformerAbstract
{
    protected $availableIncludes = [

    ];

    public function transform(Menus_model $model)
    {
        return $model->toArray();
    }
}