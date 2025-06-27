<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;
use App\Models\Agency;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rfp>
 */
class RfpFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'current_status' => $this->faker->randomElement(['draft', 'approval_pending', 'approved', 'rejected', 'published', 'closed']),
            'created_by_user_id' => User::factory(),
            'agency_id' => Agency::factory(),
            'issue_type' => $this->faker->randomElement(['integrated', 'separated_by_element', 'separated_by_group']),
            'rfp_description' => $this->faker->paragraph(),
            'closing_at' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'published_at' => $this->faker->optional()->dateTimeThisMonth(),
        ];
    }
}
