<?php

namespace Database\Factories;

use App\Models\Medicament;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medicament>
 */
class MedicamentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->unique()->words(2, true),
            'prix' => fake()->numberBetween(500, 5000),
            'stock' => fake()->numberBetween(10, 100),
            'date_expiration' => now()->addYear(),
            'description' => fake()->sentence(),
            'user_id' => User::factory(),
        ];
    }
}
