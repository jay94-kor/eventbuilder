<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Announcement;
use App\Models\Proposal;
use App\Models\Vendor;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $finalPrice = $this->faker->randomFloat(2, 1000000, 50000000);
        $prepaymentAmount = $finalPrice * 0.3; // 30% 선급금
        $balanceAmount = $finalPrice - $prepaymentAmount;

        return [
            'announcement_id' => Announcement::factory(),
            'proposal_id' => Proposal::factory(),
            'vendor_id' => Vendor::factory(),
            'final_price' => $finalPrice,
            'contract_file_path' => $this->faker->optional()->filePath(),
            'contract_signed_at' => $this->faker->optional()->dateTimeThisMonth(),
            'prepayment_amount' => $prepaymentAmount,
            'prepayment_paid_at' => $this->faker->optional()->dateTimeThisMonth(),
            'balance_amount' => $balanceAmount,
            'balance_paid_at' => $this->faker->optional()->dateTimeThisMonth(),
            'payment_status' => $this->faker->randomElement(['pending', 'prepayment_paid', 'balance_paid', 'all_paid']),
        ];
    }
}
