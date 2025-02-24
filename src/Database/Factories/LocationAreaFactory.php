<?php

declare(strict_types=1);

namespace Igniter\Local\Database\Factories;

use Igniter\Flame\Database\Factories\Factory;
use Igniter\Local\Models\LocationArea;

class LocationAreaFactory extends Factory
{
    protected $model = LocationArea::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(2),
            'type' => $this->faker->randomElement(['address', 'circle', 'polygon']),
            'color' => $this->faker->hexColor(),
            'is_default' => $this->faker->boolean(),
        ];
    }
}
