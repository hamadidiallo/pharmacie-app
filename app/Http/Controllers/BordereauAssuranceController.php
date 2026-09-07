<?php

namespace App\Http\Controllers;

use App\Enums\StatutBordereauAssurance;
use App\Models\Assurance;
use App\Models\BordereauAssurance;
use App\Models\Vente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BordereauAssuranceController extends Controller
{
    public function index(Request $request)
    {
        $query = BordereauAssurance::with(['assurance', 'user'])
            ->latest('id');

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        if ($request->filled('assurance_id')) {
            $query->where('assurance_id', $request->input('assurance_id'));
        }

        $bordereaux = $query->paginate(15)->withQueryString();
        $assurances = Assurance::orderBy('nom')->get();

        $stats = [
            'total_transmis' => BordereauAssurance::where('statut', StatutBordereauAssurance::Transmis)->sum('montant_total'),
            'total_regle' => BordereauAssurance::where('statut', StatutBordereauAssurance::Regle)->sum('montant_total'),
            'total_brouillon' => BordereauAssurance::where('statut', StatutBordereauAssurance::Brouillon)->sum('montant_total'),
        ];

        return view('bordereaux.index', compact('bordereaux', 'assurances', 'stats'));
    }

    public function create(Request $request)
    {
        $assurances = Assurance::actif()->orderBy('nom')->get();
        $assuranceId = $request->input('assurance_id');
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->toDateString());
        $dateFin = $request->input('date_fin', now()->toDateString());

        $ventesEligibles = collect();
        if ($assuranceId) {
            $ventesEligibles = Vente::with(['medicaments'])
                ->where('assurance_id', $assuranceId)
                ->whereNull('bordereau_assurance_id')
                ->where('statut_remboursement', 'en_attente')
                ->whereBetween('date_vente', [
                    $dateDebut . ' 00:00:00',
                    $dateFin . ' 23:59:59',
                ])
                ->orderBy('date_vente')
                ->get();
        }

        return view('bordereaux.create', compact('assurances', 'assuranceId', 'dateDebut', 'dateFin', 'ventesEligibles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'assurance_id' => ['required', 'exists:assurances,id'],
            'periode_debut' => ['required', 'date'],
            'periode_fin' => ['required', 'date', 'after_or_equal:periode_debut'],
            'notes' => ['nullable', 'string'],
        ]);

        $assurance = Assurance::findOrFail($validated['assurance_id']);

        return DB::transaction(function () use ($assurance, $validated) {
            $ventes = Vente::where('assurance_id', $assurance->id)
                ->whereNull('bordereau_assurance_id')
                ->where('statut_remboursement', 'en_attente')
                ->whereBetween('date_vente', [
                    $validated['periode_debut'] . ' 00:00:00',
                    $validated['periode_fin'] . ' 23:59:59',
                ])
                ->get();

            if ($ventes->isEmpty()) {
                return back()->with('alert', 'Aucune prise en charge en attente trouvée pour cet organisme sur cette période.')->withInput();
            }

            $bordereau = BordereauAssurance::create([
                'reference' => BordereauAssurance::genererReference($assurance),
                'assurance_id' => $assurance->id,
                'periode_debut' => $validated['periode_debut'],
                'periode_fin' => $validated['periode_fin'],
                'montant_total' => (float) $ventes->sum('part_assurance'),
                'nombre_dossiers' => $ventes->count(),
                'statut' => StatutBordereauAssurance::Brouillon,
                'notes' => $validated['notes'] ?? null,
                'user_id' => Auth::id(),
            ]);

            Vente::whereIn('id', $ventes->pluck('id'))
                ->update(['bordereau_assurance_id' => $bordereau->id]);

            return redirect()->route('bordereaux.show', $bordereau)
                ->with('alert', "Bordereau {$bordereau->reference} généré avec succès ({$bordereau->nombre_dossiers} dossiers).");
        });
    }

    public function show(BordereauAssurance $bordereau)
    {
        $bordereau->load(['assurance', 'user', 'ventes.medicaments']);

        return view('bordereaux.show', compact('bordereau'));
    }

    public function transmettre(BordereauAssurance $bordereau)
    {
        $bordereau->marquerTransmis();

        return redirect()->route('bordereaux.show', $bordereau)
            ->with('alert', "Bordereau {$bordereau->reference} marqué comme transmis à l'organisme.");
    }

    public function regler(Request $request, BordereauAssurance $bordereau)
    {
        $validated = $request->validate([
            'mode_reglement' => ['required', 'string', 'max:50'],
            'reference_reglement' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $bordereau->enregistrerReglement(
            mode: $validated['mode_reglement'],
            ref: $validated['reference_reglement'] ?? null,
            notes: $validated['notes'] ?? null
        );

        return redirect()->route('bordereaux.show', $bordereau)
            ->with('alert', "Règlement du bordereau {$bordereau->reference} enregistré avec succès. Factures soldées.");
    }

    public function imprimer(BordereauAssurance $bordereau)
    {
        $bordereau->load(['assurance', 'user', 'ventes.medicaments']);

        return view('bordereaux.imprimer', compact('bordereau'));
    }
}
