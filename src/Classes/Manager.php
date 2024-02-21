<?php

namespace Igniter\Local\Classes;

use Closure;
use Igniter\Flame\Geolite\Contracts\CoordinatesInterface;
use Igniter\Flame\Traits\EventEmitter;
use Igniter\Local\Contracts\LocationInterface;
use Igniter\System\Traits\SessionMaker;
use Igniter\User\Facades\AdminAuth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Location Manager Class
 */
abstract class Manager
{
    use EventEmitter;
    use SessionMaker;

    protected string $sessionKey = 'local_info';

    protected ?LocationInterface $model = null;

    protected string $locationModel = \Igniter\Local\Models\Location::class;

    protected static array $schedulesCache = [];

    /**
     * The route parameter resolver callback.
     */
    protected static Closure $locationSlugResolver;

    /**
     * Resolve the location from route parameter.
     */
    public function resolveLocationSlug(): ?string
    {
        if (isset(static::$locationSlugResolver)) {
            return call_user_func(static::$locationSlugResolver);
        }

        return request()->route('location');
    }

    /**
     * Set the location route parameter resolver callback.
     */
    public function locationSlugResolver(Closure $resolver)
    {
        static::$locationSlugResolver = $resolver;
    }

    public function check(): bool
    {
        return !is_null($this->current());
    }

    public function current(): ?LocationInterface
    {
        if (!is_null($this->model)) {
            return $this->model;
        }

        $slug = $this->resolveLocationSlug();
        if ($slug && $model = $this->getBySlug($slug)) {
            $this->setCurrent($model);
        } else {
            $id = $this->getSession('id');
            if ($id && $model = $this->getById($id)) {
                $this->setModel($model);
            }
        }

        if (is_null($this->model) && is_single_location() && $defaultLocation = $this->locationModel::getDefault()) {
            $this->setCurrent($defaultLocation);
        }

        return $this->model;
    }

    public function currentOrDefault(): ?LocationInterface
    {
        if ($model = $this->current()) {
            return $model;
        }

        if ($defaultLocation = $this->locationModel::getDefault()) {
            $this->setCurrent($defaultLocation);
        }

        return $defaultLocation;
    }

    public function currentOrAssigned(): array
    {
        if ($this->check()) {
            return [$this->getId()];
        }

        if (AdminAuth::isSuperUser()) {
            return [];
        }

        return AdminAuth::user()?->locations?->pluck('location_id')->all() ?? [];
    }

    public function setCurrent(LocationInterface $locationModel)
    {
        $this->setModel($locationModel);

        $this->putSession('id', $locationModel->getKey());

        $this->fireSystemEvent('location.current.updated', [$locationModel]);
    }

    public function getModel(): ?LocationInterface
    {
        return $this->model;
    }

    public function setModel(LocationInterface $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->current()?->getKey();
    }

    public function getName(): ?string
    {
        return $this->model?->getName();
    }

    /**
     * Creates a new instance of the location model
     */
    public function createLocationModel(): LocationInterface
    {
        $class = '\\'.ltrim($this->locationModel, '\\');

        return new $class();
    }

    /**
     * Prepares a query derived from the location model.
     */
    protected function createLocationModelQuery(): Builder
    {
        $model = $this->createLocationModel();
        $query = $model->newQuery();
        $this->extendLocationQuery($query);

        return $query;
    }

    /**
     * Extend the query used for finding the location.
     *
     * @return void
     */
    public function extendLocationQuery(Builder $query)
    {
        if (!optional(AdminAuth::getUser())->hasPermission('Admin.Locations')) {
            $query->IsEnabled();
        }
    }

    /**
     * Retrieve a location by their unique identifier.
     */
    public function getById(string|int $identifier): ?LocationInterface
    {
        $query = $this->createLocationModelQuery();

        /** @var LocationInterface $location */
        $location = $query->find($identifier);

        return $location ?: null;
    }

    /**
     * Retrieve a location by their unique slug.
     */
    public function getBySlug(string $slug): ?LocationInterface
    {
        $model = $this->createLocationModel();
        $query = $this->createLocationModelQuery();

        /** @var LocationInterface $location */
        $location = $query->where($model->getSlugKeyName(), $slug)->first();

        return $location ?: null;
    }

    public function searchByCoordinates(CoordinatesInterface $coordinates, int $limit = 20): Collection
    {
        $query = $this->createLocationModelQuery();
        $query->select('*')->selectDistance(
            $coordinates->getLatitude(),
            $coordinates->getLongitude()
        );

        return $query->orderBy('distance')->whereIsEnabled()->limit($limit)->get();
    }

    public function workingSchedule(string $type, ?int $days = null): WorkingSchedule
    {
        $cacheKey = sprintf('%s.%s', $this->getModel()->getKey(), $type);

        if (isset(self::$schedulesCache[$cacheKey])) {
            return self::$schedulesCache[$cacheKey];
        }

        $schedule = $this->getModel()->newWorkingSchedule($type, $days);

        self::$schedulesCache[$cacheKey] = $schedule;

        return $schedule;
    }
}
