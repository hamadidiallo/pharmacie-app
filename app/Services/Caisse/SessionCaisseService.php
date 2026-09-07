<?php

namespace App\Services\Caisse;

use App\Enums\StatutSessionCaisse;
use App\Models\MouvementCaisse;
use App\Models\SessionCaisse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SessionCaisseService
{
    /** Valeurs faciales des coupures et pièces en Francs CFA */
    public const COUPURES_FCFA = [
        '10000' => 'Billet de 10 000 FCFA',
        '5000'  => 'Billet de 5 000 FCFA',
        '2000'  => 'Billet de 2 000 FCFA',
        '1000'  => 'Billet de 1 000 FCFA',
        '500'   => 'Billet ou Pièce de 500 FCFA',
        '250'   => 'Pièce de 250 FCFA',
        '200'   => 'Pièce de 200 FCFA',
        '100'   => 'Pièce de 100 FCFA',
        '50'    => 'Pièce de 50 FCFA',
        '25'    => 'Pièce de 25 FCFA',
        '10'    => 'Pièce de 10 FCFA',
        '5'     => 'Pièce de 5 FCFA',
    ];

    /**
     * Récupère la session de caisse active (ouverte).
     */
    public function sessionActive(?int $userId = null): ?SessionCaisse
    {
        return SessionCaisse::ouverte()
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->latest('date_ouverture')
            ->first()
            ?? SessionCaisse::ouverte()->latest('date_ouverture')->first();
    }

    /**
     * Ouvre une nouvelle session de caisse avec le fond de caisse initial.
     */
    public function ouvrir(User $user, float $fondCaisse, ?string $notes = null): SessionCaisse
    {
        // Vérifier s'il n'y a pas déjà une session ouverte pour cet utilisateur
        $existante = SessionCaisse::ouverte()->where('user_id', $user->id)->first();
        if ($existante) {
            return $existante;
        }

        return SessionCaisse::create([
            'user_id' => $user->id,
            'date_ouverture' => now(),
            'fond_caisse_ouverture' => max(0, $fondCaisse),
            'total_especes_theorique' => 0,
            'total_mobile_money' => 0,
            'total_carte' => 0,
            'total_sorties_especes' => 0,
            'total_entrees_especes' => 0,
            'statut' => StatutSessionCaisse::Ouverte,
            'observations' => $notes,
        ]);
    }

    /**
     * Enregistre un mouvement d'espèces (dépense ou apport).
     */
    public function ajouterMouvement(
        SessionCaisse $session,
        string $type,
        float $montant,
        string $motif,
        ?string $beneficiaire,
        int $userId
    ): MouvementCaisse {
        if (!$session->estOuverte()) {
            throw new InvalidArgumentException('Impossible d\'enregistrer un mouvement sur une session de caisse clôturée.');
        }

        return DB::transaction(function () use ($session, $type, $montant, $motif, $beneficiaire, $userId) {
            $mouvement = $session->mouvements()->create([
                'user_id' => $userId,
                'type' => $type,
                'montant' => max(0, $montant),
                'motif' => $motif,
                'beneficiaire' => $beneficiaire,
            ]);

            if ($type === 'sortie') {
                $session->increment('total_sorties_especes', $montant);
            } else {
                $session->increment('total_entrees_especes', $montant);
            }

            return $mouvement;
        });
    }

    /**
     * Clôture la session de caisse avec billetage physique et calcul de l'écart.
     *
     * @param array<string, int> $billetage [valeur_faciale => quantite]
     */
    public function cloturer(
        SessionCaisse $session,
        array $billetage,
        ?string $observations = null
    ): SessionCaisse {
        return DB::transaction(function () use ($session, $billetage, $observations) {
            // Synchronisation finale des totaux
            $session->synchroniserTotaux();

            // Calcul du montant réel compté depuis le billetage
            $montantReel = 0.0;
            $billetageNettoye = [];

            foreach (array_keys(self::COUPURES_FCFA) as $valeurStr) {
                $qte = (int) ($billetage[$valeurStr] ?? 0);
                if ($qte > 0) {
                    $billetageNettoye[$valeurStr] = $qte;
                    $montantReel += ((float) $valeurStr) * $qte;
                }
            }

            $soldeTheorique = $session->soldeTheoriqueAttendu();
            $ecart = $montantReel - $soldeTheorique;

            $session->update([
                'date_fermeture' => now(),
                'montant_reel_compte' => $montantReel,
                'ecart_caisse' => $ecart,
                'billetage' => $billetageNettoye,
                'statut' => StatutSessionCaisse::Cloturee,
                'observations' => $observations ?? $session->observations,
            ]);

            return $session;
        });
    }
}
