<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicamentRequest;
use App\Models\Medicament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicamentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filtre = in_array($request->query('filtre'), ['stock', 'faible', 'rupture', 'expire'], true)
            ? $request->query('filtre')
            : 'tous';

        $recherche = trim((string) $request->query('q'));

        $medicaments = Medicament::query()
            ->when($recherche !== '', fn ($requete) => $requete->where('nom', 'LIKE', '%' . $recherche . '%'))
            ->when($filtre === 'stock', fn ($requete) => $requete->where('stock', '>', 5))
            ->when($filtre === 'faible', fn ($requete) => $requete->where('stock', '>', 0)->where('stock', '<=', 5))
            ->when($filtre === 'rupture', fn ($requete) => $requete->where('stock', 0))
            ->when($filtre === 'expire', fn ($requete) => $requete->whereBetween('date_expiration', [now(), now()->addDays(30)]))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('medicaments.index', [
            'medicaments' => $medicaments,
            'filtre' => $filtre,
            'recherche' => $recherche,
            'compteurs' => [
                'tous' => Medicament::count(),
                'faible' => Medicament::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
                'rupture' => Medicament::where('stock', 0)->count(),
                'expire' => Medicament::whereBetween('date_expiration', [now(), now()->addDays(30)])->count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('medicaments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicamentRequest $request)
    {
        // rechercher médicament identique
        $medicament = Medicament::where('nom', $request->nom)
            ->whereDate('date_expiration', $request->date_expiration)
            ->where('description', $request->description)
            ->where('prix', $request->prix)
            ->first();

        // si existe déjà
        if ($medicament) {

            // ajouter stock
            $medicament->stock += $request->stock;

            // mettre à jour prix aussi si besoin
            $medicament->prix = $request->prix;

            $medicament->save();

            return redirect()->route('medicaments.index')->with('alert', 'Stock mis à jour avec succès');
        }

        // sinon créer nouveau
        Medicament::create([
            ...$request->validated(),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('medicaments.index')->with('alert', 'Médicament ajouté avec succès');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(StoreMedicamentRequest $request, Medicament $medicament)
    {
        $medicament->update($request->validated());
        return to_route('medicaments.index')->with('alert', 'MEDICAMENT MODIFIE AVEC SUCCESS');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Medicament $medicament)
    {
        $medicament->delete();
        return to_route('medicaments.index')->with('alert', 'MEDICAMENT SUPPRIME AVEC SUCCESS');
    }
    // STOCKS MEDICAMENT METHODE
    public function stocks()
    {
        return response()->json( Medicament::select('id', 'stock')->get());
    }
}
