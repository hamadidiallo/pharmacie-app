<?php

namespace App\Http\Controllers;

use App\Caisse\Panier;
use App\Exceptions\MontantInsuffisantException;
use App\Exceptions\VenteException;
use App\Http\Requests\PaiementRequest;
use App\Models\Vente;
use App\Services\Caisse\AnnulerVenteAction;
use App\Services\Caisse\EnregistrerVenteAction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VenteController extends Controller
{
    /** Clé de session portant le panier en cours : [medicament_id => quantite]. */
    private const CLE_PANIER = 'panier';

    public function create()
    {
        try {
            // Le panier déjà en session est réinjecté dans l'écran, pour qu'un
            // retour depuis le paiement ne perde pas le travail du comptoir
            $panier = $this->panierEnSession();
        } catch (VenteException) {
            // Un produit du panier a été archivé entre-temps : on repart à vide
            session()->forget(self::CLE_PANIER);
            $panier = Panier::avec([]);
        }

        return view('ventes.create', ['panierInitial' => $panier->pourLeNavigateur()]);
    }

    /**
     * Enregistre le panier en session puis renvoie l'URL de l'écran de paiement.
     * Rien n'est écrit en base à cette étape.
     */
    public function panier(Request $request)
    {
        try {
            $quantites = Panier::normaliser($request->input('panier'));

            Panier::depuisQuantites($quantites)->controlerStock();
        } catch (VenteException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

        session([self::CLE_PANIER => $quantites]);

        return response()->json(['redirect' => route('ventes.paiement')]);
    }

    public function paiement()
    {
        try {
            $panier = $this->panierEnSession();

            if ($panier->estVide()) {
                return to_route('ventes.create')->with('alert', 'Votre panier est vide.');
            }

            $panier->controlerStock();
        } catch (VenteException $e) {
            return to_route('ventes.create')->with('alert', $e->getMessage());
        }

        $assurances = \App\Models\Assurance::actif()->orderBy('nom')->get();

        return view('ventes.paiement', [
            'panier' => $panier,
            'modes' => Vente::MODES_PAIEMENT,
            'assurances' => $assurances,
        ]);
    }

    public function store(PaiementRequest $request, EnregistrerVenteAction $enregistrerVente)
    {
        $quantites = session(self::CLE_PANIER, []);

        if ($quantites === []) {
            return to_route('ventes.create')->with('alert', 'Votre panier est vide.');
        }

        $donneesTiersPayant = [
            'avec_assurance' => $request->boolean('avec_assurance'),
            'assurance_id' => $request->input('assurance_id'),
            'matricule_assure' => $request->input('matricule_assure'),
            'nom_assure' => $request->input('nom_assure'),
            'taux_couverture' => $request->input('taux_couverture'),
        ];

        $donneesOrdonnance = [
            'nom_prescripteur' => $request->input('nom_prescripteur'),
            'specialite_prescripteur' => $request->input('specialite_prescripteur'),
            'nom_patient' => $request->input('nom_patient'),
            'age_patient' => $request->input('age_patient'),
            'date_prescription' => $request->input('date_prescription'),
            'posologie' => $request->input('posologie'),
            'notes' => $request->input('notes_ordonnance'),
        ];

        try {
            $vente = $enregistrerVente->execute(
                quantitesPanier: $quantites,
                modePaiement: $request->validated('mode_paiement'),
                montantRecu: $request->validated('montant_recu'),
                userId: Auth::id(),
                donneesTiersPayant: $donneesTiersPayant,
                donneesOrdonnance: $donneesOrdonnance
            );
        } catch (MontantInsuffisantException $e) {
            return back()->withErrors([MontantInsuffisantException::CHAMP => $e->getMessage()])->withInput();
        } catch (VenteException $e) {
            return back()->with('alert', $e->getMessage())->withInput();
        }

        session()->forget(self::CLE_PANIER);

        return to_route('ventes.show', $vente)->with('alert', 'Vente enregistrée avec succès.');
    }

    public function show(Vente $vente)
    {
        $vente->load('medicaments');

        return view('ventes.show', compact('vente'));
    }

    public function pdf(Vente $vente)
    {
        $vente->load('medicaments');

        $pdf = Pdf::loadView('ventes.pdf', compact('vente'));

        return $pdf->download('ticket-' . $vente->id . '.pdf');
    }

    public function index()
    {
        $ventes = Vente::latest()->paginate(10);

        return view('ventes.index', compact('ventes'));
    }

    public function destroy(Vente $vente, AnnulerVenteAction $annulerVente)
    {
        $annulerVente->execute($vente);

        return redirect()
            ->route('ventes.index')->with('alert', 'Vente supprimée avec succès et stock mis à jour');
    }

    /** Le panier stocké en session, reconstruit aux prix actuels. */
    private function panierEnSession(): Panier
    {
        return Panier::depuisQuantites(session(self::CLE_PANIER, []));
    }
}
