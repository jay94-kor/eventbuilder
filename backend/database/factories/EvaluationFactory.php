<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Proposal;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Evaluation>
 */
class EvaluationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'proposal_id' => Proposal::factory(),
            'evaluator_user_id' => User::factory(),
            'price_score' => $this->faker->randomFloat(2, 0, 40),
            'portfolio_score' => $this->faker->randomFloat(2, 0, 40),
            'additional_score' => $this->faker->randomFloat(2, 0, 20),
            'comment' => $this->faker->optional()->paragraph(),
        ];
    }
}
