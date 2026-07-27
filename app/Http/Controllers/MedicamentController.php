<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicamentRequest;
use App\Models\Medicament;
use Illuminate\Http\Request;

class MedicamentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $medicaments = Medicament::orderByDesc('created_at')->paginate(10);
        return view('medicaments.index', compact('medicaments'));
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
        $medicament = $request->validated();
        // rechercher médicament identique
        $medicament = Medicament::where('nom', $request->nom)
            ->where('date_expiration', $request->date_expiration)
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
            'nom' => $request->nom,
            'prix' => $request->prix,
            'stock' => $request->stock,
            'date_expiration' => $request->date_expiration,
            'description' => $request->description,
        ]);

        return redirect()->route('medicaments.index')->with('alert', 'Médicament ajouté avec succès');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Medicament $medicament)
    {
        return view('medicaments.edit', ['medicament' => $medicament]);
    }
    public function search(Request $request)
    {
        return response()->json([
            'test' => 'OK'
        ]);
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
