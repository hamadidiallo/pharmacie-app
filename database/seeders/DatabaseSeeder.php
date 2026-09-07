<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Medicament;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'firstname' => 'Admin',
                'lastname' => 'Pharmacie',
                'password' => bcrypt('password'),
            ]
        );

        $medicaments = [
            [
                'nom' => 'Paracétamol 500mg',
                'prix' => 1500,
                'stock' => 100,
                'date_expiration' => now()->addYear(),
                'description' => 'Antalgique et antipyrétique',
                'user_id' => $user->id,
            ],
            [
                'nom' => 'Amoxicilline 1g',
                'prix' => 3500,
                'stock' => 50,
                'date_expiration' => now()->addMonths(8),
                'description' => 'Antibiotique à large spectre',
                'user_id' => $user->id,
            ],
            [
                'nom' => 'Ibuprofène 400mg',
                'prix' => 2000,
                'stock' => 75,
                'date_expiration' => now()->addYear(),
                'description' => 'Anti-inflammatoire non stéroïdien',
                'user_id' => $user->id,
            ],
            [
                'nom' => 'Vitamine C 1000mg',
                'prix' => 1200,
                'stock' => 150,
                'date_expiration' => now()->addYears(2),
                'description' => 'Complément alimentaire antioxydant',
                'user_id' => $user->id,
            ],
            [
                'nom' => 'Sirop Toux Sèche',
                'prix' => 2800,
                'stock' => 30,
                'date_expiration' => now()->addMonths(6),
                'description' => 'Sirop à effet apaisant pour la toux',
                'user_id' => $user->id,
            ],
        ];

        foreach ($medicaments as $med) {
            Medicament::firstOrCreate(
                ['nom' => $med['nom']],
                $med
            );
        }

        // Fournisseurs grossistes-répartiteurs maliens
        $fournisseurs = [
            [
                'code_fournisseur' => 'LABOREX',
                'nom' => 'Laborex Mali',
                'telephone' => '+223 20 21 24 50',
                'email' => 'commandes@laborex-mali.com',
                'adresse' => 'Zone Industrielle Sotuba, Bamako',
                'delai_livraison_jours' => 1,
                'conditions_paiement' => '30 jours fin de mois',
                'ville' => 'Bamako',
                'actif' => true,
            ],
            [
                'code_fournisseur' => 'COPHARM',
                'nom' => 'Copharm Mali',
                'telephone' => '+223 20 22 45 10',
                'email' => 'contact@copharm-mali.com',
                'adresse' => 'Zone Industrielle, Bamako',
                'delai_livraison_jours' => 2,
                'conditions_paiement' => '45 jours fin de mois',
                'ville' => 'Bamako',
                'actif' => true,
            ],
            [
                'code_fournisseur' => 'UBIPHARM',
                'nom' => 'Ubipharm Mali',
                'telephone' => '+223 20 28 80 00',
                'email' => 'commandes@ubipharm.ml',
                'adresse' => 'Zone d\'Activités Diverses, Bamako',
                'delai_livraison_jours' => 1,
                'conditions_paiement' => '30 jours net',
                'ville' => 'Bamako',
                'actif' => true,
            ],
        ];

        foreach ($fournisseurs as $f) {
            \App\Models\Fournisseur::firstOrCreate(
                ['code_fournisseur' => $f['code_fournisseur']],
                $f
            );
        }

        // Assurances et organismes payeurs tiers-payant
        $assurances = [
            [
                'code' => 'CANAM',
                'nom' => 'Caisse Nationale d\'Assurance Maladie (AMO)',
                'taux_couverture_defaut' => 70.00,
                'telephone' => '+223 20 79 50 00',
                'email' => 'tierspayant@canam.ml',
                'adresse' => 'Hamdallaye ACI 2000, Bamako',
                'delai_remboursement_jours' => 30,
                'est_actif' => true,
            ],
            [
                'code' => 'INPS',
                'nom' => 'Institut National de Prévoyance Sociale (INPS)',
                'taux_couverture_defaut' => 80.00,
                'telephone' => '+223 20 22 53 01',
                'email' => 'tierspayant@inps.ml',
                'adresse' => 'Square Patrice Lumumba, Bamako',
                'delai_remboursement_jours' => 45,
                'est_actif' => true,
            ],
            [
                'code' => 'NSIA',
                'nom' => 'NSIA Assurances Mali',
                'taux_couverture_defaut' => 80.00,
                'telephone' => '+223 20 22 24 24',
                'email' => 'sante@groupensia.com',
                'adresse' => 'ACI 2000, Bamako',
                'delai_remboursement_jours' => 30,
                'est_actif' => true,
            ],
        ];

        foreach ($assurances as $a) {
            \App\Models\Assurance::firstOrCreate(
                ['code' => $a['code']],
                $a
            );
        }
    }
}
