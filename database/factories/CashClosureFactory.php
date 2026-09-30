<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\CashClosure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashClosure>
 */
class CashClosureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'business_date' => now()->toDateString(),
            'total_revenue' => '100.00',
            'expected_cash' => '60.00',
            'card_revenue' => '40.00',
            'counted_cash' => '60.00',
            'difference' => '0.00',
            'ticket_count' => 10,
            'closed_at' => now(),
        ];
    }
}
