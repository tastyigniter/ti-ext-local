<?php

namespace Igniter\Local\Models\Concerns;

use Igniter\Local\Models\LocationOption;

trait HasLocationOptions
{
    protected $optionsCache;

    public function getOptionsAttribute()
    {
        return $this->optionsCache ??= $this->all_options->pluck('value', 'item')->toArray();
    }

    public function setOptionsAttribute($value)
    {
        LocationOption::onLocation($this)->setAll($value);
    }

    public function setOption($key, $value)
    {
        $options = $this->options;
        array_set($options, $key, $value);
        $this->options = $options;
    }

    public function getOption($key = null, $default = null)
    {
        return array_get($this->options, $key, $default);
    }
}
