<?php

namespace Igniter\Local\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null resolveLocationSlug()
 * @method static void locationSlugResolver(\Closure $resolver)
 * @method static bool check()
 * @method static \Igniter\Local\Contracts\LocationInterface|null current()
 * @method static \Igniter\Local\Contracts\LocationInterface|null currentOrDefault()
 * @method static array currentOrAssigned()
 * @method static void setCurrent(\Igniter\Local\Contracts\LocationInterface $locationModel)
 * @method static \Igniter\Local\Contracts\LocationInterface|null getModel()
 * @method static \Igniter\Local\Classes\Location setModel(\Igniter\Local\Contracts\LocationInterface $model)
 * @method static int|null getId()
 * @method static string|null getName()
 * @method static \Igniter\Local\Contracts\LocationInterface createLocationModel()
 * @method static void extendLocationQuery(\Illuminate\Database\Eloquent\Builder $query)
 * @method static \Igniter\Local\Contracts\LocationInterface|null getById(string|int $identifier)
 * @method static \Igniter\Local\Contracts\LocationInterface|null getBySlug(string $slug)
 * @method static void clearInternalCache()
 * @method static void updateOrderType(void $code = null)
 * @method static void orderType()
 * @method static void checkOrderType(void $code = null)
 * @method static \Igniter\Cart\Classes\AbstractOrderType getOrderType(void $code = null)
 * @method static void getOrderTypes()
 * @method static void orderTypeIsDelivery()
 * @method static void orderTypeIsCollection()
 * @method static void hasOrderType(void $code = null)
 * @method static void getActiveOrderTypes()
 * @method static void updateScheduleTimeSlot(void $dateTime, void $isAsap = null)
 * @method static void openingSchedule()
 * @method static void deliverySchedule()
 * @method static void collectionSchedule()
 * @method static void openTime(void $type = null, void $format = null)
 * @method static void closeTime(void $type = null, void $format = null)
 * @method static void lastOrderTime()
 * @method static void checkOrderTime(void $timestamp = null, void $orderTypeCode = null)
 * @method static \Carbon\Carbon orderDateTime()
 * @method static void orderTimeIsAsap()
 * @method static void hasAsapSchedule()
 * @method static void isOpened()
 * @method static void asapScheduleTimeslot()
 * @method static void isClosed()
 * @method static void firstScheduleTimeslot()
 * @method static void scheduleTimeslot(void $orderType = null)
 * @method static void orderLeadTime()
 * @method static void orderTimeInterval()
 * @method static void checkNoOrderTypeAvailable()
 * @method static void hasLaterSchedule()
 * @method static \Igniter\Local\Classes\WorkingSchedule workingSchedule(string $type, array|int|null $days = null)
 * @method static void updateNearbyArea(\Igniter\Local\Contracts\AreaInterface $area)
 * @method static void setCoveredArea(\Igniter\Local\Classes\CoveredArea $coveredArea)
 * @method static void updateUserPosition(\Igniter\Flame\Geolite\Model\Location $position)
 * @method static void clearCoveredArea()
 * @method static void requiresUserPosition()
 * @method static void isCurrentAreaId(void $areaId)
 * @method static void getAreaId()
 * @method static \Igniter\Local\Classes\CoveredArea coveredArea()
 * @method static \Igniter\Flame\Geolite\Model\Location userPosition()
 * @method static void deliveryAreas()
 * @method static void deliveryAmount(void $cartTotal)
 * @method static void minimumOrderTotal(void $orderType = null)
 * @method static void getDeliveryChargeConditions()
 * @method static void checkMinimumOrderTotal(void $cartTotal, void $orderType = null)
 * @method static void checkDistance()
 * @method static void checkDeliveryCoverage(\Igniter\Flame\Geolite\Model\Location|null $userPosition = null)
 * @method static \Illuminate\Support\Collection searchByCoordinates(\Igniter\Flame\Geolite\Contracts\CoordinatesInterface $coordinates, int $limit = 20)
 * @method static \Igniter\Local\Classes\Location bindEvent(string $event, callable $callback, int $priority = 0)
 * @method static \Igniter\Local\Classes\Location bindEventOnce(string $event, callable $callback)
 * @method static \Igniter\Local\Classes\Location unbindEvent(string|null $event = null)
 * @method static mixed fireEvent(string $event, array $params = [], bool $halt = false)
 * @method static mixed fireSystemEvent(string $event, array $params = [], bool $halt = true)
 * @method static mixed getSession(string|null $key = null, mixed $default = null)
 * @method static void putSession(string $key, mixed $value)
 * @method static bool hasSession(string $key)
 * @method static void flashSession(string $key, mixed $value)
 * @method static void forgetSession(string $key)
 * @method static void resetSession()
 * @method static \Igniter\Local\Classes\Location setSessionKey(string $key)
 *
 * @see \Igniter\Local\Classes\Location
 */
class Location extends Facade
{
    /**
     * Get the registered name of the component.
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'location';
    }
}
