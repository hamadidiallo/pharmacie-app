<?php

namespace App\Services\Stock;

use App\Enums\StatutLot;
use App\Exceptions\StockInsuffisantException;
use App\Models\Medicament;
use App\Models\MedicamentLot;
use App\Models\Vente;
use Illuminate\Support\Facades\DB;

class FefoStockAllocator
{
    /**
     * Alloue une quantité selon la règle FEFO (First Expired, First Out).
     *
     * Consomme en priorité les lots actifs les plus proches de la date de péremption.
     *
     * @return array<int, array{lot: MedicamentLot, quantite: int}>
     *
     * @throws StockInsuffisantException
     */
    public function allouer(Medicament $medicament, int $quantiteDemandee, ?Vente $vente = null): array
    {
        if ($quantiteDemandee <= 0) {
            return [];
        }

        // Récupération des lots disponibles selon FEFO (lockForUpdate en transaction)
        $lots = MedicamentLot::where('medicament_id', $medicament->id)
            ->where('statut', StatutLot::Actif)
            ->where('quantite_actuelle', '>', 0)
            ->where('date_expiration', '>', now())
            ->orderBy('date_expiration', 'asc')
            ->lockForUpdate()
            ->get();

        $disponibleTotal = $lots->sum('quantite_actuelle');

        // Cas de transition : si aucun lot n'a encore été créé pour ce médicament mais qu'il a du stock historique
        if ($lots->isEmpty() && $medicament->stock >= $quantiteDemandee) {
            $lotAuto = MedicamentLot::create([
                'medicament_id' => $medicament->id,
                'numero_lot' => 'LOT-INIT-' . str_pad((string) $medicament->id, 4, '0', STR_PAD_LEFT),
                'date_expiration' => $medicament->date_expiration ?? now()->addYear(),
                'quantite_initiale' => $medicament->stock,
                'quantite_actuelle' => $medicament->stock,
                'statut' => StatutLot::Actif,
            ]);

            $lots = collect([$lotAuto]);
            $disponibleTotal = $medicament->stock;
        }

        if ($disponibleTotal < $quantiteDemandee) {
            throw new StockInsuffisantException(
                "Stock insuffisant pour {$medicament->nom}. Demandé : {$quantiteDemandee}, Disponible (lots valides) : {$disponibleTotal}."
            );
        }

        $allocations = [];
        $resteAAllouer = $quantiteDemandee;

        foreach ($lots as $lot) {
            if ($resteAAllouer <= 0) {
                break;
            }

            $aPrendre = min($lot->quantite_actuelle, $resteAAllouer);
            $lot->quantite_actuelle -= $aPrendre;

            if ($lot->quantite_actuelle === 0) {
                $lot->statut = StatutLot::Epuise;
            }

            $lot->save();

            $allocations[] = [
                'lot' => $lot,
                'quantite' => $aPrendre,
            ];

            // Traçabilité sur le ticket de caisse
            if ($vente) {
                DB::table('vente_medicament_lot')->insert([
                    'vente_id' => $vente->id,
                    'medicament_id' => $medicament->id,
                    'lot_id' => $lot->id,
                    'quantite' => $aPrendre,
                    'prix_unitaire' => $medicament->prix,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $resteAAllouer -= $aPrendre;
        }

        // Décrémentation du stock global du médicament
        $medicament->decrement('stock', $quantiteDemandee);

        return $allocations;
    }

    /**
     * Restitue les quantités vendues dans leurs lots respectifs en cas d'annulation de ticket.
     */
    public function restituerVente(Vente $vente): void
    {
        $lignesTracees = DB::table('vente_medicament_lot')
            ->where('vente_id', $vente->id)
            ->get();

        if ($lignesTracees->isNotEmpty()) {
            foreach ($lignesTracees as $trace) {
                $lot = MedicamentLot::find($trace->lot_id);

                if ($lot) {
                    $lot->quantite_actuelle += $trace->quantite;
                    if ($lot->statut === StatutLot::Epuise && $lot->quantite_actuelle > 0) {
                        $lot->statut = StatutLot::Actif;
                    }
                    $lot->save();
                }
            }

            DB::table('vente_medicament_lot')->where('vente_id', $vente->id)->delete();
        }
    }
}
