<?php

namespace App\Services\Caisse;

use App\Models\Vente;
use Illuminate\Support\Facades\DB;

class AnnulerVenteAction
{
    /**
     * Annule une vente de façon transactionnelle : réintègre le stock des médicaments,
     * détache les lignes de pivot et supprime l'enregistrement de vente.
     */
    public function execute(Vente $vente): void
    {
        DB::transaction(function () use ($vente) {
            // Restituer les quantités allouées dans les lots respectifs
            $allocateur = app(\App\Services\Stock\FefoStockAllocator::class);
            $allocateur->restituerVente($vente);

            // Re-créditer le stock des médicaments
            foreach ($vente->medicaments as $medicament) {
                $medicament->increment('stock', $medicament->pivot->quantite);
            }

            // Déduire les montants de la session de caisse si elle est ouverte
            if ($vente->sessionCaisse && $vente->sessionCaisse->estOuverte()) {
                if ($vente->mode_paiement === 'especes') {
                    $vente->sessionCaisse->decrement('total_especes_theorique', $vente->total);
                } elseif ($vente->mode_paiement === 'mobile_money') {
                    $vente->sessionCaisse->decrement('total_mobile_money', $vente->total);
                } elseif ($vente->mode_paiement === 'carte') {
                    $vente->sessionCaisse->decrement('total_carte', $vente->total);
                }
            }

            // Supprimer les lignes d'association
            $vente->medicaments()->detach();

            // Supprimer la vente
            $vente->delete();
        });
    }
}
