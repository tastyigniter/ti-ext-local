<?php

namespace Igniter\Local\Models\Concerns;

use Carbon\Carbon;
use Igniter\Cart\Classes\OrderTypes;
use Igniter\Local\Classes\ScheduleItem;
use Igniter\Local\Classes\WorkingSchedule;
use Igniter\Local\Events\WorkingScheduleCreatedEvent;
use Igniter\Local\Exceptions\WorkingHourException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Collection;
use InvalidArgumentException;

trait HasWorkingHours
{
    /**
     * @return Carbon
     */
    public function getCurrentTime()
    {
        traceLog('Deprecated function. No longer supported.');
    }

    public function availableWorkingTypes()
    {
        return array_merge([
            static::OPENING,
        ], collect(resolve(OrderTypes::class)->listOrderTypes())->keys()->all());
    }

    public function listWorkingHours()
    {
        traceLog('Deprecated function. Use getWorkingHours() instead.');
    }

    /**
     * @return mixed 24_7, daily or flexible
     */
    public function workingHourType($hourType = null)
    {
        return array_get($this->options, "hours.{$hourType}.type");
    }

    public function getWorkingHoursByType($type)
    {
        return $this->getWorkingHours()->groupBy('type')->get($type);
    }

    public function getWorkingHoursByDay($weekday)
    {
        return $this->getWorkingHours()->groupBy('weekday')->get($weekday);
    }

    public function getWorkingHourByDayAndType($weekday, $type)
    {
        return $this->getWorkingHoursByDay($weekday)
            ->groupBy('type')
            ->get($type)
            ->first();
    }

    public function getWorkingHourByDateAndType($date, $type)
    {
        if (!$date instanceof Carbon) {
            $date = make_carbon($date);
        }

        $weekday = $date->format('N') - 1;

        return $this->getWorkingHourByDayAndType($weekday, $type);
    }

    public function getWorkingHours()
    {
        if (!$this->hasRelation('working_hours')) {
            throw RelationNotFoundException::make($this, 'working_hours');
        }

        if (!$this->working_hours || $this->working_hours->isEmpty()) {
            $this->createDefaultWorkingHours();
        }

        return $this->working_hours;
    }

    public function newWorkingSchedule($type, $days = null)
    {
        $types = $this->availableWorkingTypes();
        if (is_null($type) || !in_array($type, $types)) {
            throw new WorkingHourException(sprintf(lang('igniter.local::default.alert_invalid_schedule_type'), $type));
        }

        if (is_null($days)) {
            $days = $this->hasFutureOrder($type) ? (int)$this->futureOrderDays($type) : 0;
        }

        $schedule = WorkingSchedule::create($days,
            $this->getWorkingHoursByType($type) ?? new Collection([])
        );

        $schedule->setType($type);

        WorkingScheduleCreatedEvent::dispatch($this, $schedule);

        return $schedule;
    }

    //
    //
    //

    public function createScheduleItem(string $type, ?array $scheduleData = null)
    {
        if (!in_array($type, $this->availableWorkingTypes())) {
            throw new InvalidArgumentException(sprintf(lang('igniter.local::default.alert_invalid_schedule_type'), $type));
        }

        $scheduleData = $scheduleData ?: array_get($this->getSettings('hours', []), $type, []);

        return ScheduleItem::create($type, $scheduleData);
    }

    public function updateSchedule($type, $scheduleData)
    {
        $this->addOpeningHours($type, $scheduleData);

        $locationHours = $this->findSettings('hours');
        $locationHours->fill([$type => $scheduleData])->save();
    }

    /**
     * Create a new or update existing location working hours
     *
     * @return bool
     */
    public function addOpeningHours(null|string|array $type, ?array $data = [])
    {
        if (is_array($type)) {
            $data = $type;
            $type = null;
        }

        if (is_null($type)) {
            foreach (['opening', 'delivery', 'collection'] as $hourType) {
                if (!is_array($scheduleData = array_get($data, $hourType))) {
                    continue;
                }

                $this->addOpeningHours($hourType, $scheduleData);
            }

            return true;
        }

        $this->working_hours()->where('type', $type)->delete();

        $scheduleItem = $this->createScheduleItem($type, $data);
        foreach ($scheduleItem->getHours() as $hours) {
            foreach ($hours as $hour) {
                $this->working_hours()->create([
                    'location_id' => $this->getKey(),
                    'weekday' => $hour['day'],
                    'type' => $type,
                    'opening_time' => mdate('%H:%i', strtotime($hour['open'])),
                    'closing_time' => mdate('%H:%i', strtotime($hour['close'])),
                    'status' => $hour['status'],
                ]);
            }
        }

        return true;
    }

    protected function parseHoursFromOptions(&$value)
    {
        // Rename options array index 'opening_hours' to 'hours'
        if (isset($value['opening_hours'])) {
            $hours = $value['opening_hours'];
            foreach (['opening', 'daily', 'delivery', 'collection'] as $type) {
                foreach (['type', 'days', 'hours'] as $suffix) {
                    if (isset($hours["{$type}_{$suffix}"])) {
                        $valueItem = $hours["{$type}_{$suffix}"];
                        if ($suffix == 'type') {
                            $valueItem = $valueItem != '24_7' ? $valueItem : '24_7';
                        }

                        $typeIndex = $type == 'daily' ? 'opening' : $type;

                        if ($suffix == 'hours') {
                            $value['hours'][$typeIndex]['open'] = $valueItem['open'] ?? '00:00';
                            $value['hours'][$typeIndex]['close'] = $valueItem['close'] ?? '23:59';
                        } else {
                            $value['hours'][$typeIndex][$suffix] = $valueItem;
                        }
                    }
                }
            }

            if (isset($hours['flexible_hours']) && is_array($hours['flexible_hours'])) {
                foreach (['opening', 'delivery', 'collection'] as $type) {
                    $value['hours'][$type]['flexible'] = $hours['flexible_hours'];
                }
            }

            unset($value['opening_hours']);
        }
    }

    protected function createDefaultWorkingHours()
    {
        foreach (['opening', 'delivery', 'collection'] as $hourType) {
            $this->addOpeningHours($hourType);
        }

        $this->reloadRelations('working_hours');
    }
}
