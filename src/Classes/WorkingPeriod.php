<?php

namespace Igniter\Local\Classes;

use ArrayAccess;
use ArrayIterator;
use Countable;
use DateInterval;
use DateTimeInterface;
use Igniter\Local\Exceptions\WorkingHourException;
use IteratorAggregate;
use Traversable;

class WorkingPeriod implements ArrayAccess, Countable, IteratorAggregate
{
    const CLOSED = 'closed';

    const OPEN = 'open';

    const OPENING = 'opening';

    /**
     * @var \Igniter\Local\Classes\WorkingRange[]
     */
    protected $ranges = [];

    public static function create($times)
    {
        $period = new static();

        $timeRanges = array_map(function($times) {
            return WorkingRange::create($times);
        }, $times);

        $period->checkWorkingRangesOverlaps($timeRanges);

        $period->ranges = $timeRanges;

        return $period;
    }

    public function isOpenAt(WorkingTime $time)
    {
        return !is_null($this->findTimeInRange($time));
    }

    public function openTimeAt(WorkingTime $time)
    {
        if ($range = $this->findTimeInRange($time)) {
            return $range->start();
        }

        return optional(current($this->ranges))->start();
    }

    public function closeTimeAt(WorkingTime $time)
    {
        if ($range = $this->findTimeInRange($time)) {
            return $range->end();
        }

        return optional(end($this->ranges))->end();
    }

    /**
     * @return bool|\Igniter\Local\Classes\WorkingTime
     */
    public function nextOpenAt(WorkingTime $time)
    {
        foreach ($this->ranges as $range) {
            if ($range->containsTime($time)) {
                if (count($this->ranges) === 1) {
                    return $range->start();
                }
                if (next($range) !== $range && $nextOpenTime = next($range)) {
                    reset($range);

                    return $nextOpenTime;
                }
            }

            if ($nextOpenTime = $this->findNextTimeInFreeTime('start', $time, $range)) {
                reset($range);

                return $nextOpenTime;
            }
        }

        return false;
    }

    /**
     * @return bool|\Igniter\Local\Classes\WorkingTime
     */
    public function nextCloseAt(WorkingTime $time)
    {
        foreach ($this->ranges as $range) {
            if ($range->containsTime($time) && $nextCloseTime = $range->end()) {
                return $nextCloseTime;
            }

            if ($nextCloseTime = $this->findNextTimeInFreeTime('end', $time, $range)) {
                return $nextCloseTime;
            }
        }

        return false;
    }

    public function opensAllDay()
    {
        $diffInHours = 0;
        foreach ($this->ranges as $range) {
            $interval = $range->start()->diff($range->end());
            $diffInHours += (int)$interval->format('%H');
        }

        return $diffInHours >= 23 || $diffInHours == 0;
    }

    public function closesLate()
    {
        foreach ($this->ranges as $range) {
            if ($range->endsNextDay()) {
                return true;
            }
        }

        return false;
    }

    public function opensLateAt(WorkingTime $time)
    {
        foreach ($this->ranges as $range) {
            if ($range->endsNextDay() && $range->containsTime($time)) {
                return true;
            }
        }

        return false;
    }

    public function timeslot(DateTimeInterface $dateTime, DateInterval $interval, ?DateInterval $leadTime = null)
    {
        return WorkingTimeslot::make($this->ranges)->generate(
            $dateTime, $interval, $leadTime
        );
    }

    protected function findTimeInRange(WorkingTime $time)
    {
        foreach ($this->ranges as $range) {
            if ($range->containsTime($time)) {
                return $range;
            }
        }
    }

    protected function findNextTimeInFreeTime($type, WorkingTime $time, WorkingRange $timeRange, ?WorkingRange &$prevTimeRange = null)
    {
        $timeOffRange = $prevTimeRange
            ? WorkingRange::create([$prevTimeRange->end(), $timeRange->start()])
            : WorkingRange::create(['00:00', $timeRange->start()]);

        if (
            $timeOffRange->containsTime($time)
            || $timeOffRange->start()->isSame($time)
        ) {
            return $timeRange->{$type}();
        }

        $prevTimeRange = $timeRange;
    }

    /**
     * @param \Igniter\Local\Classes\WorkingRange[] $ranges
     * @throws \Igniter\Local\Exceptions\WorkingHourException
     */
    protected function checkWorkingRangesOverlaps($ranges)
    {
        foreach ($ranges as $index => $range) {
            $nextRange = $ranges[$index + 1] ?? null;
            if ($nextRange && $range->overlaps($nextRange)) {
                throw new WorkingHourException(sprintf(
                    'Time ranges %s and %s overlap.',
                    $range, $nextRange
                ));
            }
        }
    }

    public function isEmpty(): bool
    {
        return empty($this->ranges);
    }

    /**
     * Retrieve an external iterator
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->ranges);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->ranges);
    }

    /**
     * Whether a offset exists
     *
     * @return bool true on success or false on failure.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->ranges[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->ranges[$offset];
    }

    /**
     * Offset to set
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new WorkingHourException('Can not set ranges');
    }

    /**
     * Offset to unset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->ranges[$offset]);
    }

    public function __toString()
    {
        $values = array_map(function($range) {
            return (string)$range;
        }, $this->ranges);

        return implode(',', $values);
    }
}
