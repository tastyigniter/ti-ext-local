<?php

namespace Igniter\Local\Models;

use Carbon\Carbon;
use Igniter\Flame\Database\Model;
use Igniter\Local\Contracts\WorkingHourInterface;
use Igniter\System\Models\Concerns\Switchable;

/**
 * Working hours Model Class
 */
class WorkingHour extends Model implements WorkingHourInterface
{
    use Switchable;

    const CLOSED = 'closed';

    const OPEN = 'open';

    const OPENING = 'opening';

    /**
     * @var string The database table name
     */
    protected $table = 'working_hours';

    protected $timeFormat = 'H:i';

    public $relation = [
        'belongsTo' => [
            'location' => [\Igniter\Local\Models\Location::class],
        ],
    ];

    protected $appends = ['day', 'open', 'close'];

    public $attributes = [
        'opening_time' => '00:00',
        'closing_time' => '23:59',
    ];

    protected $casts = [
        'weekday' => 'integer',
        'opening_time' => 'time',
        'closing_time' => 'time',
    ];

    public static $weekDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    public function getWeekDaysOptions()
    {
        return collect(self::$weekDays)->map(function($day, $index) {
            return now()->startOfWeek()->addDays($index)->isoFormat(lang('igniter::system.moment.weekday_format'));
        })->all();
    }

    public function getTimesheetOptions($value, $data)
    {
        $result = new \stdClass;
        $result->timesheet = $value ?? [];

        $result->daysOfWeek = [];
        foreach ($this->getWeekDaysOptions() as $key => $day) {
            $result->daysOfWeek[$key] = ['name' => $day];
        }

        return $result;
    }

    //
    // Accessors & Mutators
    //

    public function getDayAttribute()
    {
        return Carbon::now()->startOfWeek()->addDay($this->weekday);
    }

    public function getOpenAttribute()
    {
        $openDate = $this->getWeekDate();

        $openDate->setTimeFromTimeString($this->opening_time);

        return $openDate;
    }

    public function getCloseAttribute()
    {
        $closeDate = $this->getWeekDate();

        $closeDate->setTimeFromTimeString($this->closing_time);

        if ($this->isPastMidnight()) {
            $closeDate->addDay();
        }

        return $closeDate;
    }

    //
    // Helpers
    //

    public function isOpenAllDay()
    {
        $diffInHours = (int)floor($this->open->diffInHours($this->close));

        return $diffInHours >= 23 || $diffInHours == 0;
    }

    public function isPastMidnight()
    {
        return $this->opening_time > $this->closing_time;
    }

    public function getDay()
    {
        return $this->day->format('l');
    }

    public function getOpen()
    {
        return $this->open->format('H:i');
    }

    public function getClose()
    {
        return $this->close->format('H:i');
    }

    public function getWeekDate()
    {
        return new Carbon($this->day);
    }
}
