<?php

namespace App\Http\Controllers;

use App\Caisse\Encaissement;
use App\Caisse\Panier;
use App\Exceptions\MontantInsuffisantException;
use App\Exceptions\VenteException;
use App\Http\Requests\PaiementRequest;
use App\Models\Vente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VenteController extends Controller
{
    /** Clé de session portant le panier en cours : [medicament_id => quantite]. */
    private const CLE_PANIER = 'panier';

    public function create()
    {
        try {
            // le panier déjà en session est réinjecté dans l'écran, pour qu'un
            // retour depuis le paiement ne perde pas le travail du comptoir
            $panier = $this->panierEnSession();
        } catch (VenteException) {
            // un produit du panier a été archivé entre-temps : on repart à vide
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

        return view('ventes.paiement', [
            'panier' => $panier,
            'modes' => Vente::MODES_PAIEMENT,
        ]);
    }

    public function store(PaiementRequest $request)
    {
        $quantites = session(self::CLE_PANIER, []);

        if ($quantites === []) {
            return to_route('ventes.create')->with('alert', 'Votre panier est vide.');
        }

        try {
            $vente = DB::transaction(function () use ($quantites, $request) {

                $panier = Panier::depuisQuantites($quantites, verrouiller: true);

                $panier->controlerStock();

                $encaissement = Encaissement::pour(
                    $request->validated('mode_paiement'),
                    $panier->total(),
                    $request->validated('montant_recu')
                );

                $vente = Vente::create([
                    'total' => $panier->total(),
                    'date_vente' => now(),
                    'user_id' => Auth::id(),
                    ...$encaissement->attributs(),
                ]);

                $vente->medicaments()->attach($panier->attributsPivot());

                foreach ($panier->lignes() as $ligne) {
                    $ligne->medicament->decrement('stock', $ligne->quantite);
                }

                return $vente;
            });
        } catch (MontantInsuffisantException $e) {
            // erreur de saisie : on la remonte sur le champ concerné
            return back()->withErrors([MontantInsuffisantException::CHAMP => $e->getMessage()])->withInput();
        } catch (VenteException $e) {
            // le stock a bougé depuis l'écran de paiement
            return back()->with('alert', $e->getMessage());
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

    // VENTES SUPPRESSIONS METHODES
    public function destroy(Vente $vente)
    {
        DB::transaction(function () use ($vente) {

            // remettre stock
            foreach ($vente->medicaments as $medicament) {
                $medicament->increment('stock', $medicament->pivot->quantite);
            }

            // supprimer pivot
            $vente->medicaments()->detach();

            // supprimer vente
            $vente->delete();
        });

        return redirect()
            ->route('ventes.index')->with('alert', 'Vente supprimée avec succès et stock mis à jour');
    }

    /** Le panier stocké en session, reconstruit aux prix actuels. */
    private function panierEnSession(): Panier
    {
        return Panier::depuisQuantites(session(self::CLE_PANIER, []));
    }
}
