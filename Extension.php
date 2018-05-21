<?php namespace SamPoyigi\Local;

use SamPoyigi\Local\Classes\Location as LocationManager;

class Extension extends \System\Classes\BaseExtension
{
    public function register()
    {
        $this->app->singleton('location', function ($app) {
            $location = new LocationManager($app['session.store'], $app['events']);

            $location->setDefaultLocation(params('default_location_id'));

            return $location;
        });
    }

    public function registerComponents()
    {
        return [
            'SamPoyigi\Local\Components\Local'      => [
                'code'        => 'local',
                'name'        => 'lang:sampoyigi.local::default.component_title',
                'description' => 'lang:sampoyigi.local::default.component_desc',
            ],
            'SamPoyigi\Local\Components\Search'     => [
                'code'        => 'localSearch',
                'name'        => 'lang:sampoyigi.local::default.search.component_title',
                'description' => 'lang:sampoyigi.local::default.search.component_desc',
            ],
            'SamPoyigi\Local\Components\Menu'       => [
                'code'        => 'localMenu',
                'name'        => 'lang:sampoyigi.local::default.menu.component_title',
                'description' => 'lang:sampoyigi.local::default.menu.component_desc',
            ],
            'SamPoyigi\Local\Components\Categories' => [
                'code'        => 'categories',
                'name'        => 'lang:sampoyigi.local::default.categories.component_title',
                'description' => 'lang:sampoyigi.local::default.categories.component_desc',
            ],
            'SamPoyigi\Local\Components\Review'     => [
                'code'        => 'localReview',
                'name'        => 'lang:sampoyigi.local::default.review.component_title',
                'description' => 'lang:sampoyigi.local::default.review.component_desc',
            ],
            'SamPoyigi\Local\Components\Info'       => [
                'code'        => 'localInfo',
                'name'        => 'lang:sampoyigi.local::default.info.component_title',
                'description' => 'lang:sampoyigi.local::default.info.component_desc',
            ],
            'SamPoyigi\Local\Components\Gallery'    => [
                'code'        => 'localGallery',
                'name'        => 'lang:sampoyigi.local::default.gallery.component_title',
                'description' => 'lang:sampoyigi.local::default.gallery.component_desc',
            ],
            'SamPoyigi\Local\Components\LocalList'  => [
                'code'        => 'localList',
                'name'        => 'lang:sampoyigi.local::default.list.component_title',
                'description' => 'lang:sampoyigi.local::default.list.component_desc',
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
}
