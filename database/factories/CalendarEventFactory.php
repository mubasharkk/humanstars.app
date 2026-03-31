<?php

namespace Database\Factories;

use App\Models\Calendar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('now', '+1 month');
        $endsAt   = $this->faker->dateTimeBetween($startsAt, '+2 months');
        $type     = $this->faker->randomElement(['virtual', 'on-site']);

        return [
            'calendar_id'      => Calendar::factory(),
            'title'            => $this->faker->sentence(4),
            'description'      => $this->faker->optional()->paragraph(),
            'type'             => $type,
            'meeting_url'      => $type === 'virtual' ? $this->faker->url() : null,
            'address'          => $type === 'on-site' ? $this->faker->address() : null,
            'starts_at'        => $startsAt,
            'ends_at'          => $endsAt,
            'timezone'         => null, // inherits from calendar
            'rrule'            => null,
            'reminder_minutes' => $this->faker->optional()->randomElement([5, 10, 15, 30, 60]),
        ];
    }

    public function virtual(): static
    {
        return $this->state(fn () => [
            'type'        => 'virtual',
            'meeting_url' => $this->faker->url(),
            'address'     => null,
        ]);
    }

    public function onSite(): static
    {
        return $this->state(fn () => [
            'type'        => 'on-site',
            'meeting_url' => null,
            'address'     => $this->faker->address(),
        ]);
    }
}
