<?php

namespace Igniter\Local\Database\Factories;

use Igniter\Flame\Database\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = \Igniter\Local\Models\Review::class;

    public function definition(): array
    {
        return [
            'location_id' => $this->faker->numberBetween(1, 200),
            'customer_id' => $this->faker->numberBetween(1, 200),
            'reviewable_id' => $this->faker->numberBetween(1, 200),
            'reviewable_type' => 'orders',
            'author' => $this->faker->name,
            'quality' => $this->faker->numberBetween(0, 6),
            'delivery' => $this->faker->numberBetween(0, 6),
            'service' => $this->faker->numberBetween(0, 6),
            'review_text' => $this->faker->text(),
            'review_status' => 1,
        ];
    }
}
