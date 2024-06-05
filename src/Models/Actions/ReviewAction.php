<?php

namespace Igniter\Local\Models\Actions;

use Igniter\Local\Models\Review;
use Igniter\System\Actions\ModelAction;

class ReviewAction extends ModelAction
{
    public function __construct($model)
    {
        parent::__construct($model);

        $this->model->relation['morphMany']['review'] = [\Igniter\Local\Models\Review::class, 'name' => 'reviewable'];
    }

    public function leaveReview($attributes)
    {
        return Review::leaveReview($this->model, $attributes);
    }
}
