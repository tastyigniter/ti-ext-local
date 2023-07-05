<?php

namespace Igniter\Local;

use Igniter\Admin\Classes\MainMenuItem;
use Igniter\Admin\Classes\Navigation;
use Igniter\Admin\DashboardWidgets\Charts;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Cart\Classes\OrderTypes;
use Igniter\Cart\Models\Order;
use Igniter\Flame\Geolite\Facades\Geocoder;
use Igniter\Flame\Igniter;
use Igniter\Local\Classes\Location;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Local\Listeners\MaxOrderPerTimeslotReached;
use Igniter\Local\MainMenuWidgets\LocationPicker;
use Igniter\Local\Models\Location as LocationModel;
use Igniter\Local\Models\LocationArea;
use Igniter\Local\Models\Review;
use Igniter\Local\Models\ReviewSettings;
use Igniter\Local\Models\Scopes\LocationScope;
use Igniter\Reservation\Models\Reservation;
use Igniter\User\Facades\Auth;
use Igniter\User\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class Extension extends \Igniter\System\Classes\BaseExtension
{
    protected array $scopes = [
        LocationModel::class => LocationScope::class,
    ];

    protected $subscribe = [
        MaxOrderPerTimeslotReached::class,
    ];

    protected array $morphMap = [
        'location_areas' => \Igniter\Local\Models\LocationArea::class,
        'locations' => \Igniter\Local\Models\Location::class,
        'working_hours' => \Igniter\Local\Models\WorkingHour::class,
    ];

    public $singletons = [
        OrderTypes::class,
        'location' => Location::class
    ];

    public function register()
    {
        parent::register();

        $this->callAfterResolving('location', function (Location $location) {
            $location->setSessionKey(Igniter::runningInAdmin() ? 'admin_location' : 'location');
        });

        Route::pushMiddlewareToGroup('igniter', \Igniter\Local\Http\Middleware\CheckLocation::class);
    }

    public function boot()
    {
        View::share('showReviews', function () {
            return (bool)ReviewSettings::get('allow_reviews', false);
        });

        $this->bindRememberLocationAreaEvents();

        $this->addReviewsRelationship();
        $this->addAssetsToReviewsSettingsPage();
        $this->extendDashboardChartsDatasets();

        User::extend(function ($model) {
            $model->addDynamicMethod('getAvailableLocations', function () use ($model) {
                if ($model->locations->isEmpty() && $model->isSuperUser()) {
                    return $model->locations()->getModel()->query()->get();
                }

                return $model->locations;
            });

            $model->addDynamicMethod('isAssignedLocation', function ($location) use ($model) {
                if ($model->locations->isEmpty()) {
                    return $model->isSuperUser();
                }

                return $model->locations->contains($location);
            });
        });

        if (Igniter::runningInAdmin()) {
            $this->registerLocationsMainMenuItems();
        }
    }

    public function registerAutomationRules()
    {
        return [
            'events' => [],
            'actions' => [],
            'conditions' => [
                \Igniter\Local\AutomationRules\Conditions\ReviewCount::class,
            ],
            'presets' => [
                'chase_review_after_one_day' => [
                    'name' => 'Send a message to leave a review after 24 hours',
                    'event' => \Igniter\Automation\AutomationRules\Events\OrderSchedule::class,
                    'actions' => [
                        \Igniter\Automation\AutomationRules\Actions\SendMailTemplate::class => [
                            'template' => 'igniter.local::mail.review_chase',
                            'send_to' => 'customer',
                        ],
                    ],
                    'conditions' => [
                        \Igniter\Local\AutomationRules\Conditions\ReviewCount::class => [
                            [
                                'attribute' => 'review_count',
                                'value' => '0',
                                'operator' => 'is',
                            ],
                        ],
                        \Igniter\Cart\AutomationRules\Conditions\OrderAttribute::class => [
                            [
                                'attribute' => 'hours_since',
                                'value' => '24',
                                'operator' => 'is',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function registerCartConditions()
    {
        return [
            \Igniter\Local\CartConditions\Delivery::class => [
                'name' => 'delivery',
                'label' => 'lang:igniter.local::default.text_delivery',
                'description' => 'lang:igniter.local::default.help_delivery_condition',
            ],
        ];
    }

    public function registerComponents()
    {
        return [
            \Igniter\Local\Components\LocalBox::class => [
                'code' => 'localBox',
                'name' => 'lang:igniter.local::default.component_title',
                'description' => 'lang:igniter.local::default.component_desc',
            ],
            \Igniter\Local\Components\Search::class => [
                'code' => 'localSearch',
                'name' => 'lang:igniter.local::default.search.component_title',
                'description' => 'lang:igniter.local::default.search.component_desc',
            ],
            \Igniter\Local\Components\Review::class => [
                'code' => 'localReview',
                'name' => 'lang:igniter.local::default.review.component_title',
                'description' => 'lang:igniter.local::default.review.component_desc',
            ],
            \Igniter\Local\Components\Info::class => [
                'code' => 'localInfo',
                'name' => 'lang:igniter.local::default.info.component_title',
                'description' => 'lang:igniter.local::default.info.component_desc',
            ],
            \Igniter\Local\Components\Gallery::class => [
                'code' => 'localGallery',
                'name' => 'lang:igniter.local::default.gallery.component_title',
                'description' => 'lang:igniter.local::default.gallery.component_desc',
            ],
            \Igniter\Local\Components\LocalList::class => [
                'code' => 'localList',
                'name' => 'lang:igniter.local::default.list.component_title',
                'description' => 'lang:igniter.local::default.list.component_desc',
            ],
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'igniter.local::mail.review_chase' => 'lang:igniter.local::default.reviews.text_chase_email',
        ];
    }

    public function registerNavigation()
    {
        return [
            'restaurant' => [
                'child' => [
                    'locationsettings' => [
                        'priority' => 10,
                        'class' => 'locationsettings',
                        'href' => admin_url('locations/settings'),
                        'title' => lang('igniter.local::default.text_settings'),
                        'permission' => 'Admin.Locations',
                    ],
                ],
            ],
            'marketing' => [
                'child' => [
                    'reviews' => [
                        'priority' => 30,
                        'class' => 'reviews',
                        'href' => admin_url('igniter/local/reviews'),
                        'title' => lang('lang:igniter.local::default.reviews.side_menu'),
                        'permission' => 'Admin.Reviews',
                    ],
                ],
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'Admin.Locations' => [
                'label' => 'lang:igniter.local::default.locations_permissions',
                'group' => 'location',
            ],
            'Admin.Reviews' => [
                'description' => 'lang:igniter.local::default.reviews.permissions',
                'group' => 'location',
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'reviewsettings' => [
                'label' => 'lang:igniter.local::default.reviews.text_settings',
                'icon' => 'fa fa-gear',
                'description' => 'lang:igniter.local::default.reviews.text_settings_description',
                'model' => \Igniter\Local\Models\ReviewSettings::class,
                'permissions' => ['Admin.Reviews'],
            ],
        ];
    }

    public function registerFormWidgets()
    {
        return [
            \Igniter\Local\FormWidgets\StarRating::class => [
                'label' => 'Star Rating',
                'code' => 'starrating',
            ],
            \Igniter\Local\FormWidgets\MapArea::class => [
                'label' => 'Map Area',
                'code' => 'maparea',
            ],
            \Igniter\Local\FormWidgets\MapView::class => [
                'label' => 'Map View',
                'code' => 'mapview',
            ],
            \Igniter\Local\FormWidgets\ScheduleEditor::class => [
                'label' => 'Schedule Editor',
                'code' => 'scheduleeditor',
            ],
            \Igniter\Local\FormWidgets\SettingsEditor::class => [
                'label' => 'Settings Editor',
                'code' => 'settingseditor',
            ],
        ];
    }

    public function registerOnboardingSteps()
    {
        return [
            'igniter.local::locations' => [
                'label' => 'igniter.local::default.onboarding_locations',
                'description' => 'igniter.local::default.help_onboarding_locations',
                'icon' => 'fa-store',
                'url' => admin_url('locations'),
                'priority' => 15,
                'complete' => [\Igniter\Local\Models\Location::class, 'onboardingIsComplete'],
            ],
        ];
    }

    protected function extendDashboardChartsDatasets()
    {
        Event::listen('admin.charts.extendDatasets', function ($widget) {
            if (!$widget instanceof Charts) {
                return;
            }

            if (!ReviewSettings::get('allow_reviews', false)) {
                return;
            }

            $widget->contextDefinitions['reviews'] = [
                'label' => 'lang:igniter.local::default.reviews.text_title',
                'color' => '#FFB74D',
                'model' => Review::class,
                'column' => 'created_at',
            ];
        });
    }

    protected function addAssetsToReviewsSettingsPage()
    {
        Event::listen('admin.form.extendFieldsBefore', function ($form) {
            if (!$form->model instanceof ReviewSettings) {
                return;
            }

            $form->addJs('~/app/admin/formwidgets/repeater/assets/vendor/sortablejs/Sortable.min.js', 'sortable-js');
            $form->addJs('~/app/admin/formwidgets/repeater/assets/vendor/sortablejs/jquery-sortable.js', 'jquery-sortable-js');
            $form->addJs('~/app/admin/assets/js/ratings.js', 'ratings-js');
        });
    }

    protected function addReviewsRelationship(): void
    {
        Relation::morphMap([
            'reviews' => \Igniter\Local\Models\Review::class,
        ]);

        Order::extend(function ($model) {
            $model->relation['morphMany']['review'] = [\Igniter\Local\Models\Review::class];
        });

        Reservation::extend(function ($model) {
            $model->relation['morphMany']['review'] = [\Igniter\Local\Models\Review::class];
        });

        LocationModel::extend(function ($model) {
            $model->relation['hasMany']['reviews'] = [\Igniter\Local\Models\Review::class];

            $model->addDynamicMethod('reviews_score', function () use ($model) {
                return Review::getScoreForLocation($model->getKey());
            });
            $model->queryModifierAddSorts(['reviews_count asc', 'reviews_count desc']);
        });
    }

    protected function bindRememberLocationAreaEvents(): void
    {
        Event::listen('location.position.updated', function ($location, $position, $oldPosition) {
            $this->updateCustomerLastArea([
                'query' => $position->format(),
            ]);
        });

        Event::listen('location.area.updated', function ($location, $coveredArea) {
            $this->updateCustomerLastArea([
                'areaId' => $coveredArea->getKey(),
            ]);
        });

        Event::listen(['igniter.user.login', 'igniter.socialite.login'], function () {
            try {
                if (!strlen($lastArea = Auth::customer()->last_location_area)) {
                    return;
                }

                $lastArea = json_decode($lastArea, true);

                $searchQuery = array_get($lastArea, 'query');
                if ($searchQuery && $userPosition = Geocoder::geocode($searchQuery)->first()) {
                    LocationFacade::updateUserPosition($userPosition);
                }

                $areaId = array_get($lastArea, 'areaId');
                if ($areaId && $area = LocationArea::find($areaId)) {
                    LocationFacade::updateNearbyArea($area);
                }
            } catch (\Exception $exception) {
            }
        });
    }

    protected function updateCustomerLastArea($value)
    {
        if (!$customer = Auth::customer()) {
            return;
        }

        $lastArea = @json_decode($customer->last_location_area, true) ?: [];
        $lastArea = array_merge($lastArea, $value);

        $customer->update([
            'last_location_area' => json_encode($lastArea),
        ]);
    }

    protected function registerLocationsMainMenuItems()
    {
        AdminMenu::registerCallback(function (Navigation $manager) {
            $manager->registerMainItems([
                MainMenuItem::widget('locations', LocationPicker::class)
                    ->priority(0)
                    ->permission('Admin.Locations')
                    ->mergeConfig([
                        'form' => 'igniter.local::/models/location',
                    ]),
            ]);
        });
    }
}
