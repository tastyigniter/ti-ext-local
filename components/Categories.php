<?php namespace SamPoyigi\Local\Components;

use Admin\Models\Categories_model;

class Categories extends \System\Classes\BaseComponent
{
    public function onRender()
    {
        $this->lang->load('categories_module/categories_module');

        $this->addCss(extension_url('categories_module/assets/stylesheet.css'), 'categories-module-css');

        $this->page['categories'] = $this->loadCategories();
        $this->page['selectedCategory'] = $this->uri->segment('category');
//        $rootCategory = $this->page['categories']->first();

//        if (strlen($this->categoryParam))
//            $this->template->setTitle($rootCategory->name);
//        $this->page['countMenus'] = $this->Menus_model->getCount();
    }

    protected function loadCategories()
    {
        $query = Categories_model::with(['permalink'])->orderBy('name');

        // category must have at least one menu
        $query->whereHasMenus();

        return $query->get()->toTree();
    }

    public function buildTree($categories, $prefix = '-')
    {
        $tree = '<ul class="list-group list-group-responsive">';
        foreach ($categories as $category) {
            $tree .= partial('@tree_node', ['category' => $category]);

            if ($category->children)
                $tree .= $this->buildTree($category->children, $prefix.'-');
        }

        $tree .= '</ul>';

        return $tree;
    }
}
