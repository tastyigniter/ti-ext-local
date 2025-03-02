<?php

namespace Igniter\Local\Classes;

use Igniter\Local\Models\Location as LocationModel;

class AdminLocation extends Manager
{
    protected $sessionKey = 'admin_local_info';

    protected $locationModel = LocationModel::class;

    protected $listLocationsCache = [];

    public function current()
    {
        if (!is_null($this->model)) {
            return $this->model;
        }

        if (!$this->getAuth()->isLogged()) {
            return null;
        }

        if ($this->isSingleMode()) {
            $id = LocationModel::getDefaultKey();
        } else {
            $id = $this->getSession('id');
            if (!$id && $this->hasOneLocation() && !$this->getAuth()->isSuperUser()) {
                $id = $this->getDefaultLocation();
            }

            if ($id && !$this->isAttachedToAuth($id)) {
                $id = $this->getDefaultLocation();
            }
        }

        if ($id && $model = $this->getById($id)) {
            $this->setCurrent($model);
        }

        return $this->model;
    }

    public function clearCurrent()
    {
        $this->forgetSession();
    }

    public function hasAccess($location)
    {
        if ($this->getAuth()->isSuperUser()) {
            return true;
        }

        return $this->getAuth()->user()->hasLocationAccess($location);
    }

    public function hasRestriction()
    {
        if ($this->getAuth()->isSuperUser()) {
            return false;
        }

        return $this->getAuth()->locations()->isNotEmpty();
    }

    public function isSingleMode()
    {
        return is_single_location();
    }

    public function getId()
    {
        return optional($this->getLocation())->getKey();
    }

    public function getName()
    {
        return optional($this->getLocation())->location_name;
    }

    public function getAll()
    {
        if ($this->getAuth()->isSuperUser()) {
            return null;
        }

        return $this->getLocations()->pluck('location_id')->all();
    }

    public function getIdOrAll()
    {
        return $this->check() ? [$this->getId()] : $this->getAll();
    }

    public function getLocation()
    {
        return $this->current();
    }

    public function listLocations()
    {
        if ($this->listLocationsCache) {
            return $this->listLocationsCache;
        }

        $locations = $this->getAuth()->isSuperUser()
            ? $this->createLocationModel()->whereIsEnabled()->get()
            : $this->getLocations();

        return $this->listLocationsCache = $locations;
    }

    public static function listLocationsForMainMenuPicker($menu, $item, $user)
    {
        $instance = resolve(static::class);

        return $instance->listLocations()->map(function ($location) use ($instance) {
            return (object)[
                'id' => $location->location_id,
                'name' => $location->location_name,
                'active' => $location->location_id === $instance->getId(),
            ];
        });
    }

    public function getDefaultLocation()
    {
        if (!$staffLocation = $this->getLocations()->first()) {
            return null;
        }

        return $staffLocation->getKey();
    }

    public function hasOneLocation()
    {
        if ($this->isSingleMode()) {
            return true;
        }

        return $this->getLocations()->count() === 1;
    }

    public function hasLocations()
    {
        if ($this->isSingleMode()) {
            return false;
        }

        if ($this->getAuth()->isSuperUser()) {
            return true;
        }

        return $this->getLocations()->count() > 1;
    }

    protected function getLocations()
    {
        return $this->getAuth()
            ->locations()
            ->where('location_status', true);
    }

    /**
     * @return \Igniter\User\Auth\UserGuard
     */
    protected function getAuth()
    {
        return app('admin.auth');
    }

    protected function isAttachedToAuth($id)
    {
        return $this->listLocations()->contains(function ($model) use ($id) {
            return $model->location_id === $id;
        });
    }
}
