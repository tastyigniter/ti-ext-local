<?php

namespace Igniter\Local\Models\Concerns;

use Igniter\Flame\Exception\SystemException;
use Igniter\Flame\Igniter;
use Igniter\Local\Models\Scopes\LocationableScope;
use Igniter\User\Facades\AdminAuth;

trait Locationable
{
    /*
     * You can change the location relation name:
     *
     * const LOCATIONABLE_RELATION = 'location';
     */

    /**
     * @var bool Flag for arbitrarily enabling location scope.
     */
    public $locationScopeEnabled = false;

    /**
     * Boot the locationable trait for a model.
     *
     * @return void
     */
    public static function bootLocationable()
    {
        static::addGlobalScope(new LocationableScope);

        static::deleting(function(self $model) {
            $model->detachLocationsOnDelete();
        });
    }

    //
    //
    //

    protected function detachLocationsOnDelete()
    {
        if ($this->locationableIsSingleRelationType() || !$this->locationableIsMorphRelationType()) {
            return;
        }

        $locationable = $this->getLocationableRelationObject();

        if (Igniter::runningInAdmin() && !AdminAuth::isSuperUser() && $locationable->count() > 1) {
            throw new SystemException(lang('igniter::admin.alert_warning_locationable_delete'));
        }

        $locationable->detach();
    }

    //
    //
    //

    public function getLocationableRelationObject()
    {
        $relationName = $this->locationableRelationName();

        return $this->{$relationName}();
    }

    public function locationableIsSingleRelationType()
    {
        $relationType = $this->getRelationType($this->locationableRelationName());

        return in_array($relationType, ['hasOne', 'belongsTo', 'morphOne']);
    }

    public function locationableIsMorphRelationType()
    {
        $relationType = $this->getRelationType($this->locationableRelationName());

        return in_array($relationType, ['morphToMany', 'belongsToMany']);
    }

    public function locationableRelationName()
    {
        return defined('static::LOCATIONABLE_RELATION') ? static::LOCATIONABLE_RELATION : 'location';
    }

    public function locationableRelationExists()
    {
        $relationName = $this->locationableRelationName();

        if ($this->locationableIsSingleRelationType()) {
            return !is_null($this->{$relationName});
        }

        return count($this->{$relationName} ?? []) > 0;
    }
}
