<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Business $business): void {
            $business->members()->syncWithoutDetaching([
                $business->user_id => ['role' => Business::ROLE_OWNER, 'is_active' => true],
            ]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
        ];
    }
}
