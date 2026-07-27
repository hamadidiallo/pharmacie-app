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
    }
}
