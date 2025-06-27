<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agency>
 */
class AgencyFactory extends Factory
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
            'master_user_id' => User::factory(),
            'subscription_status' => $this->faker->randomElement(['active', 'inactive', 'trial_expired', 'payment_pending']),
            'subscription_end_date' => $this->faker->dateTimeBetween('+1 month', '+1 year'),
        ];
    }
}
