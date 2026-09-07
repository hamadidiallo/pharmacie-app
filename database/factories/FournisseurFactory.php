<?php

namespace Database\Factories;

use App\Models\Fournisseur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fournisseur>
 */
class FournisseurFactory extends Factory
{
    protected $model = Fournisseur::class;

    public function definition(): array
    {
        return [
            'nom' => fake()->company() . ' Pharma',
            'code_fournisseur' => 'FRS-' . strtoupper(fake()->bothify('##??')),
            'telephone' => '+223 ' . fake()->numerify('## ## ## ##'),
            'email' => fake()->unique()->companyEmail(),
            'adresse' => fake()->streetAddress(),
            'ville' => 'Bamako',
            'delai_livraison_jours' => fake()->numberBetween(1, 4),
            'conditions_paiement' => '30 jours fin de mois',
            'actif' => true,
        ];
    }
}
