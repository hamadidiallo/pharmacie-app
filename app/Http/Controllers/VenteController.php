<?php

namespace App\Http\Controllers;

use App\Exceptions\VenteException;
use App\Models\Medicament;
use App\Models\Vente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VenteController extends Controller
{
    public function search(Request $request)
    {
        $query = (string) $request->q;

        $medicaments = Medicament::select('id', 'nom', 'prix', 'stock')
            ->where('nom', 'LIKE', '%' . $query . '%')
            ->orderBy('nom')
            ->limit(20)
            ->get();

        return response()->json($medicaments);
    }
    public function create()
    {
        return view('ventes.create');
    }

    public function store(Request $request)
    {
        $panier = $request->input('panier');

        if (!$panier || !is_array($panier)) {
            return response()->json([
                'error' => 'Panier invalide'
            ], 422);
        }

        foreach ($panier as $item) {

            if (!isset($item['id'], $item['quantite']) || (int) $item['quantite'] < 1) {
                return response()->json([
                    'error' => 'Données panier incorrectes'
                ], 422);
            }
        }

        try {
            $vente = DB::transaction(function () use ($panier) {

                // verrouiller les lignes le temps de la transaction pour éviter
                // que deux ventes simultanées ne consomment le même stock
                $medicaments = Medicament::whereIn('id', array_column($panier, 'id'))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $lignes = [];
                $total = 0;

                // tout contrôler AVANT la moindre écriture
                foreach ($panier as $item) {

                    $medicament = $medicaments->get($item['id']);

                    if (!$medicament) {
                        throw new VenteException('Médicament introuvable', 404);
                    }

                    $quantite = (int) $item['quantite'];

                    if ($medicament->stock < $quantite) {
                        throw new VenteException('Stock insuffisant pour ' . $medicament->nom, 422);
                    }

                    // le prix vient toujours de la base, jamais du client
                    $sousTotal = $medicament->prix * $quantite;
                    $total += $sousTotal;

                    $lignes[$medicament->id] = [
                        'quantite' => $quantite,
                        'prix' => $medicament->prix,
                        'sous_total' => $sousTotal,
                    ];
                }

                $vente = Vente::create([
                    'total' => $total,
                    'date_vente' => now(),
                    'user_id' => Auth::id(),
                ]);

                $vente->medicaments()->attach($lignes);

                foreach ($lignes as $medicamentId => $ligne) {
                    $medicaments->get($medicamentId)->decrement('stock', $ligne['quantite']);
                }

                return $vente;
            });
        } catch (VenteException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], $e->getCode());
        }

        return response()->json([
            'message' => 'Vente enregistrée',
            'vente_id' => $vente->id
        ]);
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
}
