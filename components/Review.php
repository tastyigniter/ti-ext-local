<?php

namespace SamPoyigi\Local\Components;

class Review extends \System\Classes\BaseComponent
{
    public $defaultPartial = 'list';

    public $isHidden = TRUE;

    protected $list = [];

    public function onRun()
    {
        $this->list = $this->loadReviewList();
    }

    public function onRender()
    {
        $this->id = uniqid($this->alias);
        $this->page['reviewList'] = $this->list;
    }

    protected function loadReviewList()
    {
        if (!$library = $this->property('library'))
            throw new \Exception("Missing [location library] property in {$this->alias} component");

        if (!$model = $this->property('model'))
            throw new \Exception("Missing [model] property in {$this->alias} component");

        $list = $model->with([
            'location',
            'customer',
        ])->listFrontEnd([
            'page'      => $this->param('page'),
            'pageLimit' => $this->property('pageLimit', config_item('menus_page_limit')),
            'sort'      => $this->property('sort', 'date_added asc'),
            'location'  => $library->getId(),
        ]);

        return $list;
    }
}