<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Calendar>
 */
class CalendarFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'name'        => $this->faker->words(3, true),
            'color'       => $this->faker->hexColor(),
            'description' => $this->faker->optional()->sentence(),
            'timezone'    => $this->faker->timezone(),
        ];
    }
}
