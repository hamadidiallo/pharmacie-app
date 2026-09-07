<?php

namespace App\Services\Approvisionnement;

use App\Enums\StatutCommandeFournisseur;
use App\Enums\StatutLot;
use App\Models\CommandeFournisseur;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReceptionCommandeAction
{
    /**
     * Valide la réception de marchandises (Bon de Livraison) :
     * 1. Contrôle les quantités livrées par ligne.
     * 2. Crée les lots de traçabilité dans medicament_lots (numéro de lot, date d'expiration, quantité).
     * 3. Incrémente le stock vendable des médicaments.
     * 4. Met à jour le statut du bon de commande (Reçue ou Partiellement reçue).
     *
     * @param array<int, array{
     *     ligne_id: int,
     *     quantite_recue: int,
     *     numero_lot: string,
     *     date_expiration: string,
     *     date_fabrication?: string|null,
     *     prix_achat_facture?: float|null
     * }> $lignesRecues
     */
    public function execute(
        CommandeFournisseur $commande,
        string $numeroBl,
        array $lignesRecues,
        ?float $totalFacture = null
    ): CommandeFournisseur {
        if (!$commande->peutEtreRecue()) {
            throw new InvalidArgumentException("Cette commande ne peut pas être réceptionnée (statut actuel : {$commande->statut->libelle()}).");
        }

        return DB::transaction(function () use ($commande, $numeroBl, $lignesRecues, $totalFacture) {
            $toutesEntierementLivrees = true;

            foreach ($lignesRecues as $dataLigne) {
                $ligne = $commande->lignes()->findOrFail($dataLigne['ligne_id']);
                $qteRecueArrivage = (int) ($dataLigne['quantite_recue'] ?? 0);

                if ($qteRecueArrivage <= 0) {
                    if ($ligne->resteALivrer() > 0) {
                        $toutesEntierementLivrees = false;
                    }
                    continue;
                }

                $medicament = $ligne->medicament;
                $numLot = trim($dataLigne['numero_lot'] ?? '') ?: ('LOT-' . strtoupper(\Illuminate\Support\Str::random(6)));
                $dateExp = $dataLigne['date_expiration'] ?? now()->addYears(2)->format('Y-m-d');
                $prixAchat = isset($dataLigne['prix_achat_facture']) && $dataLigne['prix_achat_facture'] !== ''
                    ? (float) $dataLigne['prix_achat_facture']
                    : (float) $ligne->prix_achat_unitaire_estime;

                // Création du lot dans l'inventaire FEFO
                $lot = $medicament->lots()->create([
                    'numero_lot' => $numLot,
                    'quantite_initiale' => $qteRecueArrivage,
                    'quantite_actuelle' => $qteRecueArrivage,
                    'date_expiration' => $dateExp,
                    'date_fabrication' => $dataLigne['date_fabrication'] ?? null,
                    'prix_achat_unitaire' => $prixAchat,
                    'statut' => StatutLot::Actif,
                ]);

                // Incrémenter le stock global du médicament
                $medicament->increment('stock', $qteRecueArrivage);

                // Mise à jour de la date d'expiration globale du médicament si le stock précédent était à 0 ou expiré
                if ($medicament->date_expiration < now() || $medicament->stock === $qteRecueArrivage) {
                    $medicament->update(['date_expiration' => $dateExp]);
                }

                // Enregistrement sur la ligne de commande
                $nouvelleQteTotaleRecue = $ligne->quantite_recue + $qteRecueArrivage;
                $ligne->update([
                    'quantite_recue' => $nouvelleQteTotaleRecue,
                    'prix_achat_unitaire_facture' => $prixAchat,
                    'numero_lot_recu' => $numLot,
                    'date_expiration_recue' => $dateExp,
                    'medicament_lot_id' => $lot->id,
                ]);

                if ($nouvelleQteTotaleRecue < $ligne->quantite_commandee) {
                    $toutesEntierementLivrees = false;
                }
            }

            // Statut de la commande
            $nouveauStatut = $toutesEntierementLivrees
                ? StatutCommandeFournisseur::Recue
                : StatutCommandeFournisseur::PartiellementRecue;

            $commande->update([
                'numero_bl' => $numeroBl,
                'total_facture' => $totalFacture ?? $commande->total_estime,
                'date_reception' => now(),
                'statut' => $nouveauStatut,
            ]);

            return $commande;
        });
    }
}
