<?php namespace Igniter\Local\Components;

use Admin\Models\Categories_model;
use Location;
use Main\Template\Page;

class Categories extends \System\Classes\BaseComponent
{
    public function defineProperties()
    {
        return [
            'menusPage' => [
                'label' => 'Menu Page',
                'type' => 'text',
                'default' => 'local/menus',
            ],
        ];
    }

    public static function getMenusPageOptions()
    {
        return Page::lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['categories'] = $categories = $this->loadCategories();
        $this->page['selectedCategory'] = $this->findSelectedCategory($categories);
    }

    protected function loadCategories()
    {
        $query = Categories_model::orderBy('name');

        if (!$location = Location::current())
            $query->whereHasOrDoesntHaveLocation($location->getKey());

        return $query->get()->toTree();
    }

    protected function findSelectedCategory($categories)
    {
        $slug = $this->param('category');
        if (!strlen($slug))
            return null;

        return $categories->where('permalink_slug', $slug)->first();
    }
}
