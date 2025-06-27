<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Rfp;
use App\Models\RfpElement;
use App\Models\Agency;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rfp_id' => Rfp::factory(),
            'rfp_element_id' => null, // 통합 발주일 경우 null
            'agency_id' => Agency::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'estimated_price' => $this->faker->randomFloat(2, 1000000, 100000000),
            'closing_at' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'channel_type' => $this->faker->randomElement(['agency_private', 'public']),
            'contact_info_private' => $this->faker->boolean(),
            'published_at' => $this->faker->optional()->dateTimeThisMonth(),
            'status' => $this->faker->randomElement(['open', 'closed', 'awarded']),
            'evaluation_criteria' => [
                'quality' => $this->faker->numberBetween(20, 40),
                'price' => $this->faker->numberBetween(20, 40),
                'experience' => $this->faker->numberBetween(10, 30),
                'schedule' => $this->faker->numberBetween(10, 20),
            ],
        ];
    }

    public function separated(): static
    {
        return $this->state(fn (array $attributes) => [
            'rfp_element_id' => RfpElement::factory(),
        ]);
    }
}
