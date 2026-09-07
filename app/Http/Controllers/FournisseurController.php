<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    public function index(Request $request)
    {
        $recherche = trim((string) $request->q);

        $fournisseurs = Fournisseur::query()
            ->withCount('commandes')
            ->when($recherche !== '', function ($q) use ($recherche) {
                $q->where('nom', 'LIKE', "%{$recherche}%")
                  ->orWhere('code_fournisseur', 'LIKE', "%{$recherche}%")
                  ->orWhere('telephone', 'LIKE', "%{$recherche}%");
            })
            ->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        return view('fournisseurs.index', [
            'fournisseurs' => $fournisseurs,
            'recherche' => $recherche,
        ]);
    }

    public function store(Request $request)
    {
        $donnees = $request->validate([
            'nom' => 'required|string|max:150',
            'code_fournisseur' => 'nullable|string|max:50|unique:fournisseurs,code_fournisseur',
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'delai_livraison_jours' => 'required|integer|min:1|max:30',
            'conditions_paiement' => 'nullable|string|max:100',
        ]);

        $fournisseur = Fournisseur::create($donnees);

        return redirect()->route('fournisseurs.index')
            ->with('alert', "Fournisseur « {$fournisseur->nom} » ajouté avec succès au répertoire.");
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $donnees = $request->validate([
            'nom' => 'required|string|max:150',
            'code_fournisseur' => "nullable|string|max:50|unique:fournisseurs,code_fournisseur,{$fournisseur->id}",
            'telephone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'delai_livraison_jours' => 'required|integer|min:1|max:30',
            'conditions_paiement' => 'nullable|string|max:100',
            'actif' => 'boolean',
        ]);

        $fournisseur->update($donnees);

        return redirect()->route('fournisseurs.index')
            ->with('alert', "Fiche du fournisseur « {$fournisseur->nom} » mise à jour.");
    }

    public function destroy(Fournisseur $fournisseur)
    {
        if ($fournisseur->commandes()->exists()) {
            $fournisseur->update(['actif' => false]);
            return redirect()->route('fournisseurs.index')
                ->with('alert', "Le fournisseur « {$fournisseur->nom} » a des commandes rattachées. Il a été désactivé du répertoire.");
        }

        $fournisseur->delete();

        return redirect()->route('fournisseurs.index')
            ->with('alert', "Le fournisseur « {$fournisseur->nom} » a été supprimé.");
    }
}
