<?php

namespace App\Services\Approvisionnement;

use App\Enums\StatutCommandeFournisseur;
use App\Models\CommandeFournisseur;
use App\Models\Fournisseur;
use App\Models\Medicament;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GenerateurCommandeAutomatique
{
    /**
     * Détecte tous les médicaments nécessitant un réassort selon le stock de sécurité et les ruptures.
     *
     * @return Collection<int, array{medicament: Medicament, quantite_suggeree: int, prix_estime: float}>
     */
    public function detecterBesoinsReassort(): Collection
    {
        $medicaments = Medicament::query()
            ->where(function ($q) {
                $q->whereColumn('stock', '<=', 'stock_securite')
                  ->orWhere('stock', '<=', 5);
            })
            ->orderBy('nom')
            ->get();

        return $medicaments->map(function (Medicament $medicament) {
            $seuil = max($medicament->stock_securite ?? 10, 10);
            $quantiteVoulue = ($seuil * 2) - $medicament->stock;
            $quantiteSuggeree = max(10, $quantiteVoulue);

            // Prix d'achat estimé : si non renseigné sur le dernier lot, estimation à ~70% du prix de vente
            $dernierLot = $medicament->lots()->latest('id')->first();
            $prixEstime = $dernierLot?->prix_achat_unitaire
                ? (float) $dernierLot->prix_achat_unitaire
                : round($medicament->prix * 0.70);

            return [
                'medicament' => $medicament,
                'stock_actuel' => $medicament->stock,
                'stock_securite' => $seuil,
                'quantite_suggeree' => (int) $quantiteSuggeree,
                'prix_achat_estime' => (float) $prixEstime,
                'sous_total_estime' => (float) ($quantiteSuggeree * $prixEstime),
            ];
        });
    }

    /**
     * Crée un bon de commande brouillon automatique auprès du fournisseur spécifié.
     */
    public function genererBonCommande(
        Fournisseur $fournisseur,
        User $createur,
        ?array $articlesSelectionnes = null
    ): CommandeFournisseur {
        return DB::transaction(function () use ($fournisseur, $createur, $articlesSelectionnes) {
            $besoins = $this->detecterBesoinsReassort();

            // Filtrer par articles sélectionnés si fourni
            if ($articlesSelectionnes !== null) {
                $besoins = $besoins->filter(fn ($b) => in_array($b['medicament']->id, $articlesSelectionnes, true));
            }

            $datePrevue = now()->addDays($fournisseur->delai_livraison_jours ?? 2);

            $commande = CommandeFournisseur::create([
                'fournisseur_id' => $fournisseur->id,
                'user_id' => $createur->id,
                'reference' => CommandeFournisseur::genererReference(),
                'date_commande' => now(),
                'date_livraison_prevue' => $datePrevue,
                'statut' => StatutCommandeFournisseur::Brouillon,
                'total_estime' => 0,
                'notes' => 'Généré automatiquement selon le seuil de sécurité et les ruptures de stock.',
            ]);

            $total = 0;

            foreach ($besoins as $item) {
                $commande->lignes()->create([
                    'medicament_id' => $item['medicament']->id,
                    'quantite_commandee' => $item['quantite_suggeree'],
                    'quantite_recue' => 0,
                    'prix_achat_unitaire_estime' => $item['prix_achat_estime'],
                ]);

                $total += $item['sous_total_estime'];
            }

            $commande->update(['total_estime' => $total]);

            return $commande;
        });
    }
}
