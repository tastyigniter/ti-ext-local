<?php namespace SamPoyigi\Local\Components;

use Admin\Models\Categories_model;

class Categories extends \System\Classes\BaseComponent
{
    public function defineProperties()
    {
        return [
            'menusPage' => [
                'label' => 'lang:sampoyigi.local::default.label_menus_page',
                'type'  => 'text',
                'default'  => 'local/menus',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['selectedCategory'] = $this->param('category');
        $this->page['categories'] = $this->loadCategories();
    }

    protected function loadCategories()
    {
        $query = Categories_model::orderBy('name');

        // category must have at least one menu
//        $query->whereHasMenus();

        return $query->get()->toTree();
    }
}
