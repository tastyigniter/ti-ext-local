<?php namespace SamPoyigi\Local;

use Event;

class Extension extends \System\Classes\BaseExtension
{
    public function initialize()
    {
        Event::listen('controller.beforeConstructor', function ($controller) {
        });
    }

    public function registerComponents()
    {
        return [
            'SamPoyigi\Local\Components\Local'      => [
                'code'        => 'local',
                'name'        => 'lang:local::default.local.component_title',
                'description' => 'lang:local::default.local.component_desc',
            ],
            'SamPoyigi\Local\Components\Search'     => [
                'code'        => 'localSearch',
                'name'        => 'lang:local::default.search.component_title',
                'description' => 'lang:local::default.search.component_desc',
            ],
            'SamPoyigi\Local\Components\Menu'       => [
                'code'        => 'localMenu',
                'name'        => 'lang:local::default.menu.component_title',
                'description' => 'lang:local::default.menu.component_desc',
            ],
            'SamPoyigi\Local\Components\Categories' => [
                'code'        => 'categories',
                'name'        => 'lang:local::default.categories.component_title',
                'description' => 'lang:local::default.categories.component_desc',
            ],
            'SamPoyigi\Local\Components\Review'     => [
                'code'        => 'localReview',
                'name'        => 'lang:local::default.review.component_title',
                'description' => 'lang:local::default.review.component_desc',
            ],
            'SamPoyigi\Local\Components\Info'       => [
                'code'        => 'localInfo',
                'name'        => 'lang:local::default.info.component_title',
                'description' => 'lang:local::default.info.component_desc',
            ],
            'SamPoyigi\Local\Components\Gallery'    => [
                'code'        => 'localGallery',
                'name'        => 'lang:local::default.gallery.component_title',
                'description' => 'lang:local::default.gallery.component_desc',
            ],
            'SamPoyigi\Local\Components\LocalList'  => [
                'code'        => 'localList',
                'name'        => 'lang:local::default.list.component_title',
                'description' => 'lang:local::default.list.component_desc',
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'Module.LocalModule' => [
                'action'      => ['manage'],
                'description' => 'Ability to manage local extension settings',
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Local Settings',
                'description' => 'Manage location settings.',
                'icon'        => '',
                'model'       => 'SamPoyigi\Local\Models\Settings_model',
                'permissions' => ['Module.LocalModule'],
            ],
        ];
    }
}
