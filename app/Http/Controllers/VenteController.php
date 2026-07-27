<?php

namespace App\Http\Controllers;

use App\Models\Medicament;
use App\Models\Vente;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Adapter\PDFLib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VenteController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->q;

        $medicaments = Medicament::where('nom', 'LIKE', "%$query%")
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

        $total = 0;

        foreach ($panier as $item) {

            if (!isset($item['id'], $item['prix'], $item['quantite'])) {
                return response()->json([
                    'error' => 'Données panier incorrectes'
                ], 422);
            }

            $total += $item['prix'] * $item['quantite'];
        }

        $vente = Vente::create([
            'total' => $total,
            'date_vente' => now(),
            'user_id' => Auth::user()->id,
        ]);

        foreach ($panier as $item) {

            $medicament = Medicament::find($item['id']);

            if (!$medicament) {
                return response()->json([
                    'error' => 'Médicament introuvable'
                ], 404);
            }

            if ($medicament->stock < $item['quantite']) {
                return response()->json([
                    'error' => 'Stock insuffisant pour ' . $medicament->nom
                ], 422);
            }

            $vente->medicaments()->attach($medicament->id, [
                'quantite' => $item['quantite'],
                'prix' => $item['prix'],
                'sous_total' => $item['prix'] * $item['quantite']
            ]);

            $medicament->stock -= $item['quantite'];
            $medicament->save();
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
        // remettre stock

        foreach ($vente->medicaments as $medicament) {

            $medicament->stock += $medicament->pivot->quantite;

            $medicament->save();
        }

        // supprimer pivot
        $vente->medicaments()->detach();

        // supprimer vente
        $vente->delete();

        return redirect()
            ->route('ventes.index')->with('alert', 'Vente supprimée avec succès et stock mis à jour');
    }
}
