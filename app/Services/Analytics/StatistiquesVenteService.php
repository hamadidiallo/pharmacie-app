<?php

namespace App\Services\Analytics;

use App\Models\Medicament;
use App\Models\Vente;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatistiquesVenteService
{
    /**
     * Calcule le chiffre d'affaires jour par jour sur les N derniers jours (aujourd'hui inclus).
     * Les jours sans vente sont représentés avec un montant à zéro.
     *
     * @return array<int, array{jour: string, date: string, total: float}>
     */
    public function caParJour(int $nbJours = 7): array
    {
        $debut = today()->subDays($nbJours - 1);

        $totaux = Vente::whereBetween('date_vente', [$debut->copy()->startOfDay(), today()->endOfDay()])
            ->get(['date_vente', 'total'])
            ->groupBy(fn (Vente $vente) => $vente->date_vente->toDateString())
            ->map(fn ($ventes) => (float) $ventes->sum('total'));

        $serie = [];

        for ($i = 0; $i < $nbJours; $i++) {
            $jour = $debut->copy()->addDays($i);

            $serie[] = [
                'jour' => $jour->translatedFormat('D'),
                'date' => $jour->toDateString(),
                'total' => $totaux->get($jour->toDateString(), 0.0),
            ];
        }

        return $serie;
    }

    /**
     * Analyse des ventes et stocks pour la page de statistiques selon la période demandée.
     *
     * @return array<string, mixed>
     */
    public function analyserPeriode(string $periode): array
    {
        $periodeValidee = in_array($periode, ['semaine', 'mois', 'trimestre'], true)
            ? $periode
            : 'mois';

        [$debut, $fin, $libelle] = match ($periodeValidee) {
            'semaine' => [now()->startOfWeek(), now()->endOfWeek(), 'Cette semaine'],
            'trimestre' => [Carbon::now()->firstOfQuarter(), Carbon::now()->lastOfQuarter(), 'Ce trimestre'],
            default => [now()->startOfMonth(), now()->endOfMonth(), now()->translatedFormat('F Y')],
        };

        $topMedicaments = DB::table('medicament__vente')
            ->join('medicaments', 'medicaments.id', '=', 'medicament__vente.medicament_id')
            ->join('ventes', 'ventes.id', '=', 'medicament__vente.vente_id')
            ->whereBetween('ventes.date_vente', [$debut, $fin])
            ->select(
                'medicaments.nom',
                DB::raw('SUM(medicament__vente.quantite) as total_quantite'),
                DB::raw('SUM(medicament__vente.sous_total) as total_montant')
            )
            ->groupBy('medicaments.nom')
            ->orderByDesc('total_quantite')
            ->limit(5)
            ->get();

        $surveillance = Medicament::aSurveiller()
            ->orderBy('stock')
            ->limit(15)
            ->get();

        return [
            'periode' => $periodeValidee,
            'libellePeriode' => $libelle,
            'caPeriode' => (float) Vente::whereBetween('date_vente', [$debut, $fin])->sum('total'),
            'nbVentes' => Vente::whereBetween('date_vente', [$debut, $fin])->count(),
            'topMedicaments' => $topMedicaments,
            'serieCa' => $this->caParJour(7),
            'surveillance' => $surveillance,
        ];
    }
}
