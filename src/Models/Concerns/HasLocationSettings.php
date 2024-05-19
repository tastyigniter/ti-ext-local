<?php

namespace Igniter\Local\Models\Concerns;

use Igniter\Local\Models\LocationSettings;

trait HasLocationSettings
{
    public function getSettings(string $item, mixed $default = null): mixed
    {
        return array_get($this->grouped_settings, $item, $default);
    }

    public function findSettings(string $item): LocationSettings
    {
        return $this->settings()->firstOrNew(['item' => $item]);
    }

    public function getGroupedSettingsAttribute(): mixed
    {
        return $this->settings->mapWithKeys(function($setting) {
            return [$setting->item => $setting->data];
        })->all();
    }
}
