<?php

namespace App\Http\Controllers;

use App\Exceptions\VenteException;
use App\Models\Medicament;
use App\Models\Vente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VenteController extends Controller
{
    /** Clé de session portant le panier en cours : [medicament_id => quantite]. */
    private const CLE_PANIER = 'panier';

    public function search(Request $request)
    {
        $query = (string) $request->q;

        $medicaments = Medicament::select('id', 'nom', 'prix', 'stock', 'description')
            ->where('nom', 'LIKE', '%' . $query . '%')
            ->orderBy('nom')
            ->limit(20)
            ->get();

        return response()->json($medicaments);
    }

    public function create()
    {
        // le panier déjà en session est réinjecté dans l'écran, pour qu'un
        // retour depuis le paiement ne perde pas le travail du comptoir
        $panierInitial = array_values(array_map(
            fn (array $ligne) => [
                'id' => $ligne['medicament']->id,
                'nom' => $ligne['medicament']->nom,
                'prix' => $ligne['prix'],
                'stock' => $ligne['medicament']->stock,
                'quantite' => $ligne['quantite'],
            ],
            $this->lignesDuPanier(session(self::CLE_PANIER, []))
        ));

        return view('ventes.create', compact('panierInitial'));
    }

    /**
     * Enregistre le panier en session puis renvoie l'URL de l'écran de paiement.
     * Rien n'est écrit en base à cette étape.
     */
    public function panier(Request $request)
    {
        $panier = $request->input('panier');

        if (!is_array($panier) || $panier === []) {
            return response()->json(['error' => 'Le panier est vide'], 422);
        }

        $quantites = [];

        foreach ($panier as $item) {

            if (!isset($item['id'], $item['quantite']) || (int) $item['quantite'] < 1) {
                return response()->json(['error' => 'Données panier incorrectes'], 422);
            }

            // un même produit peut être envoyé deux fois : on cumule
            $quantites[(int) $item['id']] = ($quantites[(int) $item['id']] ?? 0) + (int) $item['quantite'];
        }

        try {
            $this->lignesDuPanier($quantites, controlerStock: true);
        } catch (VenteException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

        session([self::CLE_PANIER => $quantites]);

        return response()->json(['redirect' => route('ventes.paiement')]);
    }

    public function paiement()
    {
        $quantites = session(self::CLE_PANIER, []);

        if ($quantites === []) {
            return to_route('ventes.create')->with('alert', 'Votre panier est vide.');
        }

        try {
            $lignes = $this->lignesDuPanier($quantites, controlerStock: true);
        } catch (VenteException $e) {
            return to_route('ventes.create')->with('alert', $e->getMessage());
        }

        return view('ventes.paiement', [
            'lignes' => $lignes,
            'total' => array_sum(array_column($lignes, 'sous_total')),
            'modes' => Vente::MODES_PAIEMENT,
        ]);
    }

    public function store(Request $request)
    {
        $quantites = session(self::CLE_PANIER, []);

        if ($quantites === []) {
            return to_route('ventes.create')->with('alert', 'Votre panier est vide.');
        }

        $donnees = $request->validate([
            'mode_paiement' => ['required', Rule::in(array_keys(Vente::MODES_PAIEMENT))],
            'montant_recu' => ['nullable', 'numeric', 'min:0'],
        ], [
            'mode_paiement.required' => 'Choisissez un mode de paiement.',
            'mode_paiement.in' => 'Ce mode de paiement n\'est pas reconnu.',
            'montant_recu.numeric' => 'Le montant reçu doit être un nombre.',
        ]);

        try {
            $vente = DB::transaction(function () use ($quantites, $donnees) {

                // verrouiller les lignes le temps de la transaction pour éviter
                // que deux ventes simultanées ne consomment le même stock
                $lignes = $this->lignesDuPanier($quantites, controlerStock: true, verrouiller: true);

                $total = array_sum(array_column($lignes, 'sous_total'));

                $montantRecu = null;
                $monnaieRendue = null;

                if ($donnees['mode_paiement'] === 'especes') {

                    $montantRecu = (float) ($donnees['montant_recu'] ?? 0);

                    if ($montantRecu < $total) {
                        throw ValidationException::withMessages([
                            'montant_recu' => 'Le montant reçu est inférieur au total à payer.',
                        ]);
                    }

                    $monnaieRendue = $montantRecu - $total;
                }

                $vente = Vente::create([
                    'total' => $total,
                    'date_vente' => now(),
                    'user_id' => Auth::id(),
                    'mode_paiement' => $donnees['mode_paiement'],
                    'montant_recu' => $montantRecu,
                    'monnaie_rendue' => $monnaieRendue,
                ]);

                $vente->medicaments()->attach(array_map(
                    fn (array $ligne) => [
                        'quantite' => $ligne['quantite'],
                        'prix' => $ligne['prix'],
                        'sous_total' => $ligne['sous_total'],
                    ],
                    $lignes
                ));

                foreach ($lignes as $ligne) {
                    $ligne['medicament']->decrement('stock', $ligne['quantite']);
                }

                return $vente;
            });
        } catch (VenteException $e) {
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

    /**
     * Reconstruit les lignes du panier à partir des quantités, en relisant
     * toujours le prix en base : il n'est jamais accepté depuis le client.
     *
     * @param  array<int,int>  $quantites  [medicament_id => quantite]
     * @return array<int,array{medicament: Medicament, quantite: int, prix: float, sous_total: float}>
     *
     * @throws VenteException
     */
    private function lignesDuPanier(array $quantites, bool $controlerStock = false, bool $verrouiller = false): array
    {
        if ($quantites === []) {
            return [];
        }

        $requete = Medicament::whereIn('id', array_keys($quantites));

        if ($verrouiller) {
            $requete->lockForUpdate();
        }

        $medicaments = $requete->get()->keyBy('id');

        $lignes = [];

        foreach ($quantites as $id => $quantite) {

            $medicament = $medicaments->get($id);

            if (!$medicament) {
                throw new VenteException('Médicament introuvable', 404);
            }

            if ($controlerStock && $medicament->stock < $quantite) {
                throw new VenteException('Stock insuffisant pour ' . $medicament->nom, 422);
            }

            $lignes[$medicament->id] = [
                'medicament' => $medicament,
                'quantite' => $quantite,
                'prix' => (float) $medicament->prix,
                'sous_total' => (float) $medicament->prix * $quantite,
            ];
        }

        return $lignes;
    }
}
