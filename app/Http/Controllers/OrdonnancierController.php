<?php

namespace App\Http\Controllers;

use App\Models\Medicament;
use App\Models\OrdonnancierLigne;
use Illuminate\Http\Request;

class OrdonnancierController extends Controller
{
    public function index(Request $request)
    {
        $query = OrdonnancierLigne::with(['medicament', 'lot', 'pharmacien', 'vente'])
            ->latest('date_delivrance');

        if ($request->filled('recherche')) {
            $terme = '%' . $request->input('recherche') . '%';
            $query->where(function ($q) use ($terme) {
                $q->where('numero_ordonnancier', 'like', $terme)
                    ->orWhere('nom_prescripteur', 'like', $terme)
                    ->orWhere('nom_patient', 'like', $terme);
            });
        }

        if ($request->filled('tableau')) {
            $query->whereHas('medicament', function ($q) use ($request) {
                $q->where('tableau', $request->input('tableau'));
            });
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_delivrance', '>=', $request->input('date_debut'));
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_delivrance', '<=', $request->input('date_fin'));
        }

        $lignes = $query->paginate(20)->withQueryString();

        $stats = [
            'total_inscriptions' => OrdonnancierLigne::count(),
            'total_stupefiants' => OrdonnancierLigne::whereHas('medicament', fn ($q) => $q->where('tableau', 'Stupéfiant'))->count(),
            'total_liste_1' => OrdonnancierLigne::whereHas('medicament', fn ($q) => $q->where('tableau', 'Liste I'))->count(),
            'total_liste_2' => OrdonnancierLigne::whereHas('medicament', fn ($q) => $q->where('tableau', 'Liste II'))->count(),
        ];

        return view('ordonnancier.index', compact('lignes', 'stats'));
    }

    public function imprimer(Request $request)
    {
        $query = OrdonnancierLigne::with(['medicament', 'lot', 'pharmacien', 'vente'])
            ->orderBy('date_delivrance', 'asc');

        if ($request->filled('tableau')) {
            $query->whereHas('medicament', function ($q) use ($request) {
                $q->where('tableau', $request->input('tableau'));
            });
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_delivrance', '>=', $request->input('date_debut'));
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date_delivrance', '<=', $request->input('date_fin'));
        }

        $lignes = $query->get();

        return view('ordonnancier.imprimer', compact('lignes'));
    }
}
