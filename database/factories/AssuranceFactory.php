<?php

namespace Database\Factories;

use App\Models\Assurance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssuranceFactory extends Factory
{
    protected $model = Assurance::class;

    public function definition(): array
    {
        return [
            'nom' => fake()->company() . ' Assurances',
            'code' => strtoupper(fake()->unique()->lexify('ASS???')),
            'taux_couverture_defaut' => fake()->randomElement([70.00, 80.00, 100.00]),
            'telephone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'adresse' => fake()->address(),
            'delai_remboursement_jours' => 30,
            'est_actif' => true,
        ];
    }
}
