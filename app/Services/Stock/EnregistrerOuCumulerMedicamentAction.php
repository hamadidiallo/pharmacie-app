<?php

namespace App\Services\Stock;

use App\Models\Medicament;

class EnregistrerOuCumulerMedicamentAction
{
    /**
     * Enregistre un nouveau médicament ou cumule son stock s'il existe déjà à l'identique
     * (même nom, même date d'expiration, même description et même prix).
     *
     * @param array<string, mixed> $donnees
     * @return array{medicament: Medicament, cumule: bool}
     */
    public function execute(array $donnees, ?int $userId = null): array
    {
        $existant = Medicament::where('nom', $donnees['nom'])
            ->whereDate('date_expiration', $donnees['date_expiration'])
            ->where('description', $donnees['description'])
            ->where('prix', $donnees['prix'])
            ->first();

        if ($existant) {
            $existant->stock += (int) $donnees['stock'];
            $existant->prix = $donnees['prix'];
            $existant->save();

            $numeroLot = $donnees['numero_lot'] ?? ('LOT-' . strtoupper(\Illuminate\Support\Str::random(6)));
            $existant->lots()->create([
                'numero_lot' => $numeroLot,
                'date_expiration' => $donnees['date_expiration'],
                'quantite_initiale' => $donnees['stock'],
                'quantite_actuelle' => $donnees['stock'],
                'statut' => \App\Enums\StatutLot::Actif,
            ]);

            return [
                'medicament' => $existant,
                'cumule' => true,
            ];
        }

        // Nettoyage de numero_lot pour ne pas planter Medicament::create
        $champsMedicament = collect($donnees)->except('numero_lot')->all();

        $nouveau = Medicament::create([
            ...$champsMedicament,
            'user_id' => $userId,
        ]);

        $numeroLot = $donnees['numero_lot'] ?? ('LOT-' . strtoupper(\Illuminate\Support\Str::random(6)));
        $nouveau->lots()->create([
            'numero_lot' => $numeroLot,
            'date_expiration' => $donnees['date_expiration'],
            'quantite_initiale' => $donnees['stock'],
            'quantite_actuelle' => $donnees['stock'],
            'statut' => \App\Enums\StatutLot::Actif,
        ]);

        return [
            'medicament' => $nouveau,
            'cumule' => false,
        ];
    }
}
