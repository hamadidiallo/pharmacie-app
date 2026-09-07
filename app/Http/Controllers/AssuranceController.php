<?php

namespace App\Http\Controllers;

use App\Models\Assurance;
use Illuminate\Http\Request;

class AssuranceController extends Controller
{
    public function index()
    {
        $assurances = Assurance::withCount(['ventesEnAttente', 'bordereaux'])
            ->withSum('ventesEnAttente as total_creances_en_attente', 'part_assurance')
            ->orderBy('nom')
            ->paginate(15);

        $totalCreancesGlobales = Assurance::withSum('ventesEnAttente as creances', 'part_assurance')
            ->get()
            ->sum('creances') ?? 0;

        return view('assurances.index', compact('assurances', 'totalCreancesGlobales'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:assurances,code'],
            'taux_couverture_defaut' => ['required', 'numeric', 'min:0', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string'],
            'delai_remboursement_jours' => ['required', 'integer', 'min:1'],
            'est_actif' => ['boolean'],
        ]);

        $validated['est_actif'] = $request->boolean('est_actif', true);

        Assurance::create($validated);

        return redirect()->route('assurances.index')
            ->with('alert', 'Organisme payeur ajouté avec succès.');
    }

    public function update(Request $request, Assurance $assurance)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:assurances,code,' . $assurance->id],
            'taux_couverture_defaut' => ['required', 'numeric', 'min:0', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string'],
            'delai_remboursement_jours' => ['required', 'integer', 'min:1'],
            'est_actif' => ['boolean'],
        ]);

        $validated['est_actif'] = $request->boolean('est_actif', true);

        $assurance->update($validated);

        return redirect()->route('assurances.index')
            ->with('alert', 'Organisme payeur mis à jour avec succès.');
    }

    public function destroy(Assurance $assurance)
    {
        if ($assurance->ventes()->exists() || $assurance->bordereaux()->exists()) {
            return redirect()->route('assurances.index')
                ->with('alert', 'Impossible de supprimer cet organisme car des dossiers ou bordereaux lui sont rattachés. Vous pouvez le désactiver.');
        }

        $assurance->delete();

        return redirect()->route('assurances.index')
            ->with('alert', 'Organisme payeur supprimé avec succès.');
    }
}
