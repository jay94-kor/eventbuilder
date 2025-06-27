<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'business_registration_number' => $this->faker->numerify('###-##-#####'),
            'address' => $this->faker->address(),
            'description' => $this->faker->text(200),
            'specialties' => $this->faker->randomElements(['stage', 'lighting', 'sound', 'equipment', 'decoration'], 2),
            'master_user_id' => User::factory(),
            'status' => $this->faker->randomElement(['active', 'suspended', 'permanently_banned']),
            'ban_reason' => null,
            'banned_at' => null,
        ];
    }

    public function banned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'permanently_banned',
            'ban_reason' => $this->faker->sentence(),
            'banned_at' => $this->faker->dateTimeThisYear(),
        ]);
    }
}
