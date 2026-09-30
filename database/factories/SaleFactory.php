<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
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
            'customer_id' => null,
            'total' => fake()->randomFloat(2, 1, 100),
            'payment_method' => fake()->randomElement([Sale::PAYMENT_CASH, Sale::PAYMENT_CARD]),
            'checkout_token' => (string) Str::uuid(),
            'sold_at' => now(),
        ];
    }
}
