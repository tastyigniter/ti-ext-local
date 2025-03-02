<?php

namespace Igniter\Local\Http\Actions;

use Igniter\Local\Facades\AdminLocation;
use Igniter\System\Classes\ControllerAction;
use Illuminate\Support\Facades\Event;

class LocationAwareController extends ControllerAction
{
    /**
     * Define controller location configuration array.
     *  $locationConfig = [
     *      'applyScopeOnListQuery'  => true',
     *      'applyScopeOnFormQuery'  => true',
     *      'addAbsenceConstraint'  => false',
     *  ];
     * @var array
     */
    public $locationConfig;

    public $requiredProperties = [];

    /**
     * @var array Required controller configuration array keys
     */
    protected $requiredConfig = [];

    /**
     * List_Controller constructor.
     *
     * @param \Illuminate\Routing\Controller $controller
     *
     * @throws \Exception
     */
    public function __construct($controller)
    {
        parent::__construct($controller);

        $this->locationConfig = $controller->locationConfig;

        // Build configuration
        $this->setConfig($controller->locationConfig, $this->requiredConfig);

        $this->hideAction([
            'locationApplyScope',
        ]);

        $this->controller->bindEvent('controller.beforeRemap', function () {
            $this->locationBindEvents();
        });
    }

    public function locationApplyScope($query)
    {
        if (!in_array(\Igniter\Local\Models\Concerns\Locationable::class, class_uses($query->getModel()))) {
            return;
        }

        if (is_null($ids = AdminLocation::getIdOrAll())) {
            return;
        }

        (bool)$this->getConfig('addAbsenceConstraint', true)
            ? $query->whereHasOrDoesntHaveLocation($ids)
            : $query->whereHasLocation($ids);
    }

    protected function locationBindEvents()
    {
        if ($this->controller->isClassExtendedWith(\Igniter\Admin\Http\Actions\ListController::class)) {
            Event::listen('admin.list.extendQuery', function ($listWidget, $query) {
                if ((bool)$this->getConfig('applyScopeOnListQuery', true)) {
                    $this->locationApplyScope($query);
                }
            });

            Event::listen('admin.filter.extendQuery', function ($filterWidget, $query, $scope) {
                if (array_get($scope->config, 'locationAware') === true
                    && (bool)$this->getConfig('applyScopeOnListQuery', true)
                ) {
                    $this->locationApplyScope($query);
                }
            });
        }

        if ($this->controller->isClassExtendedWith(\Igniter\Admin\Http\Actions\FormController::class)) {
            $this->controller->bindEvent('admin.controller.extendFormQuery', function ($query) {
                if ((bool)$this->getConfig('applyScopeOnFormQuery', true)) {
                    $this->locationApplyScope($query);
                }
            });
        }
    }
}
