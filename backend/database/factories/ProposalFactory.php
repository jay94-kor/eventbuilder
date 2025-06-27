<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Announcement;
use App\Models\Vendor;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Proposal>
 */
class ProposalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'announcement_id' => Announcement::factory(),
            'vendor_id' => Vendor::factory(),
            'proposed_price' => $this->faker->randomFloat(2, 1000000, 50000000),
            'proposal_text' => $this->faker->paragraph(),
            'proposal_file_path' => $this->faker->optional()->filePath(),
            'status' => $this->faker->randomElement(['submitted', 'under_review', 'awarded', 'rejected']),
            'reserve_rank' => null,
        ];
    }

    public function winner(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'awarded',
        ]);
    }

    public function reserve(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'reserve_rank' => $this->faker->numberBetween(1, 3),
        ]);
    }
}
