<?php

namespace App\Http\Controllers;

use App\Enums\StatutLot;
use App\Enums\TableauReglementaire;
use App\Http\Requests\StoreMedicamentRequest;
use App\Models\Medicament;
use App\Models\MedicamentLot;
use App\Services\Stock\EnregistrerOuCumulerMedicamentAction;
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
            ->with(['lots' => fn ($q) => $q->orderBy('date_expiration', 'asc')])
            ->when($recherche !== '', function ($requete) use ($recherche) {
                $requete->where(function ($q) use ($recherche) {
                    $q->where('nom', 'LIKE', '%' . $recherche . '%')
                      ->orWhere('dci', 'LIKE', '%' . $recherche . '%')
                      ->orWhere('code_barre', $recherche);
                });
            })
            ->when($filtre === 'stock', fn ($requete) => $requete->enStock())
            ->when($filtre === 'faible', fn ($requete) => $requete->stockFaible())
            ->when($filtre === 'rupture', fn ($requete) => $requete->enRupture())
            ->when($filtre === 'expire', fn ($requete) => $requete->procheExpiration())
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('medicaments.index', [
            'medicaments' => $medicaments,
            'filtre' => $filtre,
            'recherche' => $recherche,
            'tableaux' => TableauReglementaire::cases(),
            'compteurs' => [
                'tous' => Medicament::count(),
                'faible' => Medicament::stockFaible()->count(),
                'rupture' => Medicament::enRupture()->count(),
                'expire' => Medicament::procheExpiration()->count(),
            ],
        ]);
    }

    /** Recherche comptoir (nom commercial, DCI ou scan douchette code-barres). */
    public function search(Request $request)
    {
        $q = trim((string) $request->q);

        if ($q === '') {
            return response()->json([]);
        }

        $medicaments = Medicament::select(
            'id', 'nom', 'dci', 'code_barre', 'forme', 'dosage', 'tableau',
            'ordonnance_requise', 'prix', 'stock', 'description', 'date_expiration'
        )
            ->with(['lotsActifs'])
            ->where(function ($requete) use ($q) {
                $requete->where('code_barre', $q)
                    ->orWhere('nom', 'LIKE', '%' . $q . '%')
                    ->orWhere('dci', 'LIKE', '%' . $q . '%');
            })
            ->orderBy('nom')
            ->limit(20)
            ->get();

        return response()->json($medicaments);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('medicaments.create', [
            'tableaux' => TableauReglementaire::cases(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicamentRequest $request, EnregistrerOuCumulerMedicamentAction $action)
    {
        $resultat = $action->execute($request->validated(), Auth::id());

        $message = $resultat['cumule']
            ? 'Stock et lot mis à jour avec succès'
            : 'Médicament et lot initial créés avec succès';

        return redirect()->route('medicaments.index')->with('alert', $message);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreMedicamentRequest $request, Medicament $medicament)
    {
        $medicament->update($request->validated());
        return to_route('medicaments.index')->with('alert', 'Médicament mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Medicament $medicament)
    {
        $medicament->delete();

        return to_route('medicaments.index')
            ->with('alert', $medicament->nom . ' a été retiré du catalogue. Les ventes passées sont conservées.');
    }

    /**
     * Ajout d'un nouveau lot d'arrivage pour un médicament.
     */
    public function ajouterLot(Request $request, Medicament $medicament)
    {
        $donnees = $request->validate([
            'numero_lot' => 'required|string|max:80',
            'date_expiration' => 'required|date|after:today',
            'date_fabrication' => 'nullable|date',
            'quantite' => 'required|integer|min:1',
            'prix_achat_unitaire' => 'nullable|numeric|min:0',
        ]);

        $medicament->lots()->create([
            'numero_lot' => $donnees['numero_lot'],
            'date_expiration' => $donnees['date_expiration'],
            'date_fabrication' => $donnees['date_fabrication'] ?? null,
            'quantite_initiale' => $donnees['quantite'],
            'quantite_actuelle' => $donnees['quantite'],
            'prix_achat_unitaire' => $donnees['prix_achat_unitaire'] ?? null,
            'statut' => StatutLot::Actif,
        ]);

        // Synchronisation du stock global
        $medicament->increment('stock', $donnees['quantite']);

        return back()->with('alert', "Lot {$donnees['numero_lot']} ajouté avec succès (+{$donnees['quantite']} unités).");
    }

    /**
     * Isolement d'urgence d'un lot (Quarantaine / Rappel sanitaire ANRP).
     */
    public function isolerLot(Request $request, MedicamentLot $lot)
    {
        $donnees = $request->validate([
            'motif' => 'required|string|max:255',
            'statut' => 'required|in:isole,rappele',
        ]);

        $lot->statut = $donnees['statut'] === 'rappele' ? StatutLot::Rappele : StatutLot::Isole;
        $lot->motif_isolement = $donnees['motif'];
        $lot->save();

        // Recalcul du stock disponible à la vente
        $lot->medicament->synchroniserStockDepuisLots();

        $libelle = $lot->statut->libelle();
        return back()->with('alert', "Le lot {$lot->numero_lot} a été placé sous statut : {$libelle}. Il est immédiatement retiré de la vente.");
    }

    /**
     * Réactivation d'un lot après levée de quarantaine.
     */
    public function reactiverLot(MedicamentLot $lot)
    {
        $lot->statut = StatutLot::Actif;
        $lot->motif_isolement = null;
        $lot->save();

        $lot->medicament->synchroniserStockDepuisLots();

        return back()->with('alert', "Le lot {$lot->numero_lot} est de nouveau actif et disponible à la vente.");
    }
}
