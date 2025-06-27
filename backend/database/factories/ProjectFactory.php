<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Agency;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDateTime = $this->faker->dateTimeBetween('+1 week', '+3 months');
        $endDateTime = $this->faker->dateTimeBetween($startDateTime, '+1 year');
        $preparationStart = $this->faker->dateTimeBetween('-1 week', $startDateTime);
        $철수End = $this->faker->dateTimeBetween($endDateTime, $endDateTime->format('Y-m-d') . ' +1 week');

        return [
            'project_name' => $this->faker->sentence(3),
            'start_datetime' => $startDateTime,
            'end_datetime' => $endDateTime,
            'preparation_start_datetime' => $preparationStart,
            '철수_end_datetime' => $철수End,
            'client_name' => $this->faker->company(),
            'client_contact_person' => $this->faker->name(),
            'client_contact_number' => $this->faker->phoneNumber(),
            'main_agency_contact_user_id' => User::factory(),
            'sub_agency_contact_user_id' => User::factory(),
            'agency_id' => Agency::factory(),
            'is_indoor' => $this->faker->boolean(),
            'location' => $this->faker->address(),
            'budget_including_vat' => $this->faker->randomFloat(2, 10000000, 1000000000),
        ];
    }
}
