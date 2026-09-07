<?php

namespace App\Services\Caisse;

use App\Caisse\Encaissement;
use App\Caisse\Panier;
use App\Exceptions\VenteException;
use App\Models\Assurance;
use App\Models\OrdonnancierLigne;
use App\Models\SessionCaisse;
use App\Models\Vente;
use App\Services\Stock\FefoStockAllocator;
use Illuminate\Support\Facades\DB;

class EnregistrerVenteAction
{
    /**
     * Enregistre une vente de façon transactionnelle avec débit du stock
     * et association des médicaments au pivot, tiers-payant et ordonnancier.
     *
     * @param array<int, int> $quantitesPanier [medicament_id => quantite]
     * @param string $modePaiement
     * @param numeric|null $montantRecu
     * @param int|null $userId
     * @param array<string, mixed> $donneesTiersPayant
     * @param array<string, mixed> $donneesOrdonnance
     * @return Vente
     *
     * @throws \App\Exceptions\VenteException
     * @throws \App\Exceptions\MontantInsuffisantException
     */
    public function execute(
        array $quantitesPanier,
        string $modePaiement,
        mixed $montantRecu = null,
        ?int $userId = null,
        array $donneesTiersPayant = [],
        array $donneesOrdonnance = []
    ): Vente {
        return DB::transaction(function () use ($quantitesPanier, $modePaiement, $montantRecu, $userId, $donneesTiersPayant, $donneesOrdonnance) {
            $panier = Panier::depuisQuantites($quantitesPanier, verrouiller: true);

            $panier->controlerStock();

            // Contrôle réglementaire de l'ordonnance
            $exigeOrdonnance = $panier->contientMedicamentSousOrdonnance();
            if ($exigeOrdonnance) {
                if (empty($donneesOrdonnance['nom_prescripteur']) || empty($donneesOrdonnance['nom_patient'])) {
                    throw new VenteException(
                        "La délivrance d'un médicament sous ordonnance (Liste I, II ou Stupéfiant) exige obligatoirement le nom du prescripteur et du patient.",
                        422
                    );
                }
            }

            // Calcul du tiers payant
            $totalBrut = $panier->total();
            $assuranceId = null;
            $matriculeAssure = null;
            $nomAssure = null;
            $tauxCouverture = null;
            $partAssurance = 0.0;
            $partPatient = $totalBrut;
            $statutRemboursement = 'non_applicable';

            if (!empty($donneesTiersPayant['avec_assurance']) && !empty($donneesTiersPayant['assurance_id'])) {
                $assurance = Assurance::findOrFail($donneesTiersPayant['assurance_id']);
                $assuranceId = $assurance->id;
                $matriculeAssure = $donneesTiersPayant['matricule_assure'] ?? null;
                $nomAssure = $donneesTiersPayant['nom_assure'] ?? null;
                $tauxCouverture = (float) ($donneesTiersPayant['taux_couverture'] ?? $assurance->taux_couverture_defaut);
                $partAssurance = round($totalBrut * ($tauxCouverture / 100), 2);
                $partPatient = round($totalBrut - $partAssurance, 2);
                $statutRemboursement = 'en_attente';
            }

            // Le montant réellement encaissé auprès du patient au comptoir correspond à la part patient (ticket modérateur)
            $encaissement = Encaissement::pour(
                $modePaiement,
                $partPatient,
                $montantRecu
            );

            // Recherche d'une session de caisse ouverte
            $session = SessionCaisse::ouverte()
                ->when($userId, fn ($q) => $q->where('user_id', $userId))
                ->latest('date_ouverture')
                ->first()
                ?? SessionCaisse::ouverte()->latest('date_ouverture')->first();

            $vente = Vente::create([
                'total' => $totalBrut,
                'date_vente' => now(),
                'user_id' => $userId,
                'session_caisse_id' => $session?->id,
                'assurance_id' => $assuranceId,
                'matricule_assure' => $matriculeAssure,
                'nom_assure' => $nomAssure,
                'taux_couverture' => $tauxCouverture,
                'part_assurance' => $partAssurance,
                'part_patient' => $partPatient,
                'statut_remboursement' => $statutRemboursement,
                ...$encaissement->attributs(),
            ]);

            $vente->medicaments()->attach($panier->attributsPivot());

            $allocateur = app(FefoStockAllocator::class);
            $allocations = [];
            foreach ($panier->lignes() as $ligne) {
                $allocations[$ligne->medicament->id] = $allocateur->allouer($ligne->medicament, $ligne->quantite, $vente);
            }

            // Inscription au registre de l'ordonnancier réglementaire
            if ($exigeOrdonnance || !empty($donneesOrdonnance['nom_prescripteur'])) {
                $lignesAInscrire = $exigeOrdonnance ? $panier->lignesSousOrdonnance() : $panier->lignes();
                foreach ($lignesAInscrire as $ligne) {
                    $premierLotId = $allocations[$ligne->medicament->id][0]['lot']->id ?? null;
                    OrdonnancierLigne::create([
                        'numero_ordonnancier' => OrdonnancierLigne::genererNumero(),
                        'vente_id' => $vente->id,
                        'medicament_id' => $ligne->medicament->id,
                        'medicament_lot_id' => $premierLotId,
                        'date_prescription' => $donneesOrdonnance['date_prescription'] ?? now()->toDateString(),
                        'date_delivrance' => now(),
                        'nom_prescripteur' => $donneesOrdonnance['nom_prescripteur'],
                        'specialite_prescripteur' => $donneesOrdonnance['specialite_prescripteur'] ?? null,
                        'nom_patient' => $donneesOrdonnance['nom_patient'],
                        'age_patient' => $donneesOrdonnance['age_patient'] ?? null,
                        'posologie' => $donneesOrdonnance['posologie'] ?? null,
                        'quantite_delivree' => $ligne->quantite,
                        'pharmacien_id' => $userId,
                        'notes' => $donneesOrdonnance['notes'] ?? null,
                    ]);
                }
            }

            // Mise à jour de la session de caisse avec le montant net encaissé
            if ($session) {
                if ($modePaiement === 'especes') {
                    $session->increment('total_especes_theorique', $partPatient);
                } elseif ($modePaiement === 'mobile_money') {
                    $session->increment('total_mobile_money', $partPatient);
                } elseif ($modePaiement === 'carte') {
                    $session->increment('total_carte', $partPatient);
                }
            }

            return $vente;
        });
    }
}
