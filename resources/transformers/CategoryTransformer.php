<?php namespace Igniter\Local\Resources\Transformers;

use Admin\Models\Categories_model;

class CategoryTransformer extends \League\Fractal\TransformerAbstract
{
    public function transform(Categories_model $model)
    {
        return $model->toArray();
    }
}