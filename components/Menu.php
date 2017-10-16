<?php

namespace SamPoyigi\Local\Components;

use Igniter\Models\Categories_model;

class Menu extends \System\Classes\BaseComponent
{
//    public $defaultPartial = 'list';

    public $isHidden = TRUE;

    public $isGrouped = FALSE;

    protected $list = [];

    public function onRender()
    {
        $this->id = uniqid($this->alias);
        $menuList = $this->loadMenuList();
        $this->page['showMenuImages'] = config_item('show_menu_images');
//        $this->page['categoryCount'] = $this->list->toBase()->getCountForPagination();
        $this->page['menuList'] = $menuList; //->get()->keyBy('category_id');
        $this->page['groupMenuList'] = $this->isGrouped;
//        $this->page['menuItems'] = $menuList->keyBy('category_id');
//        $this->page['menuLinks'] = $menuList->links();
    }

    protected function loadMenuList()
    {
        if (!$library = $this->property('library'))
            throw new \Exception("Missing [location library] property in {$this->alias} component");

        if (!$model = $this->property('model'))
            throw new \Exception("Missing [model] property in {$this->alias} component");

        if ($model instanceof Categories_model) {
            $this->isGrouped = TRUE;
            $list = $this->loadGroupedList($model);
        }
        else {
            $list = $this->loadList($model);
        }

        return $list;
    }

    protected function loadList($model)
    {
        $list = $model->with([
            'categories.permalink',
            'special',
            'mealtime',
            'menu_options',
        ])->listFrontEnd([
            'page'      => $this->param('page'),
            'pageLimit' => $this->property('pageLimit', config_item('menus_page_limit')),
            'sort'      => $this->property('sort', 'menu_priority asc'),
            'group'     => $this->property('group', 'categories.category_id'),
            'category'  => $this->param('category'),
        ]);

        return $list;
    }

    protected function loadGroupedList($model)
    {
        $query = $model->with([
            'permalink',
            'menus' => function ($menusQuery) {
                $menusQuery->listFrontEnd([
                    'page'      => $this->param('page'),
                    'pageLimit' => $this->property('pageLimit', config_item('menus_page_limit')),
                    'sort'      => $this->property('sort', 'menu_priority asc'),
                    'group'     => $this->property('group', 'categories.category_id'),
                    'category'  => null, //$this->param('category'),
                ]);
            },
            'menus.special',
            'menus.mealtime',
            'menus.menu_options',
        ]);

        $query->whereHasMenus();

        if ($this->param('category'))
            $query->whereSlug($this->param('category'));

        $list = $query->get();

        return $list;
    }
}