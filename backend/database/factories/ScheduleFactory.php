<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;
use App\Models\Announcement;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDateTime = $this->faker->dateTimeBetween('+1 day', '+1 month');
        $endDateTime = $this->faker->dateTimeBetween($startDateTime, '+2 months');

        return [
            'schedulable_id' => Project::factory(),
            'schedulable_type' => 'App\\Models\\Project',
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'location' => $this->faker->address(),
            'status' => $this->faker->randomElement(['planned', 'ongoing', 'completed', 'cancelled']),
            'type' => $this->faker->randomElement(['meeting', 'delivery', 'installation', 'dismantling', 'rehearsal', 'event_execution']),
        ];
    }

    public function forAnnouncement(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedulable_id' => Announcement::factory(),
            'schedulable_type' => 'App\\Models\\Announcement',
        ]);
    }
}
