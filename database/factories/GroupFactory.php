<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => $this->faker->words(2, true) . ' Team',
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
