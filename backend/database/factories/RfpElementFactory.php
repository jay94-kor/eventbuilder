<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Rfp;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RfpElement>
 */
class RfpElementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $elementTypes = ['stage', 'lighting', 'sound', 'equipment', 'decoration', 'security'];
        $allocatedBudget = $this->faker->randomFloat(2, 1000000, 20000000);
        $prepaymentRatio = $this->faker->randomFloat(2, 0.2, 0.5); // 20-50%
        $balanceRatio = 1.0 - $prepaymentRatio;

        return [
            'rfp_id' => Rfp::factory(),
            'element_type' => $this->faker->randomElement($elementTypes),
            'details' => [
                'quantity' => $this->faker->numberBetween(1, 10),
                'specifications' => $this->faker->paragraph(),
                'requirements' => $this->faker->paragraph(),
                'delivery_date' => $this->faker->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
            ],
            'allocated_budget' => $allocatedBudget,
            'prepayment_ratio' => $prepaymentRatio,
            'prepayment_due_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'balance_ratio' => $balanceRatio,
            'balance_due_date' => $this->faker->dateTimeBetween('+1 month', '+3 months'),
        ];
    }
}
